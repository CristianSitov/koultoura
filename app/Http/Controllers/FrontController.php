<?php

namespace App\Http\Controllers;

use App\Mail\EventRegistrationConfirmation;
use App\Models\Day;
use App\Models\Presentation;
use App\Models\Person;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Jetstream\Jetstream;

class FrontController extends Controller
{
    /**
     * fallback: return Inertia::render('Splash');
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('Home', $this->getPageData());
    }

    public function schedule(): Response
    {
        return Inertia::render('Schedule', $this->getPageData());
    }

    public function eventRegistration(Request $request): RedirectResponse
    {
        $input = $request->input();
        $input['name'] = implode(' ', [$input['first_name'], $input['last_name']]);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'required_with:email_confimation', 'same:email_confirmation'],
            'email_confirmation' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            'job' => ['required', 'string'],
            'organization' => ['required', 'string'],
            'country' => ['required', 'string'],
            'event_details' => ['required'],
            'event_details.*' => ['numeric', 'in:1,2,3'],
        ])->validate();

        $user = User::create([
            'slug' => Str::uuid(),
            'name' => $input['name'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'password' => Hash::make(Str::uuid()),
        ]);

        $user->profile()->create([
            'user_id' => $user->id,
            'phone' => $input['phone'],
            'job' => $input['job'],
            'organization' => $input['organization'],
            'country' => $input['country'],
            'event_details' => json_encode(['days' => $input['event_details']], JSON_THROW_ON_ERROR),
        ]);

        Mail::to($user)
            ->bcc(env('MAIL_FROM_ADDRESS'))
            ->send(new EventRegistrationConfirmation($user->load('profile')));

        return Redirect::route('confirmation', ['id' => $user->slug]);
    }

    public function dashboard(Request $request): Response
    {
        return Inertia::render('Dashboard');
    }

    public function subscribersList(Request $request, int $day = 0, int $volunteers = 0): Response
    {
        return Inertia::render('Tools/Subscribers', [
            'day' => $day,
            'volunteers' => $volunteers,
            'subscribers' => $this->getSubscribersList($day, $volunteers),
        ]);
    }

    public function subscribersListPdf(Request $request): \Illuminate\Http\Response
    {
        $day = $request->get('day', false);
        $volunteersOnly = $request->get('volunteers', false);

        $data = $this->getSubscribersList($day, $volunteersOnly)
            ->whereNull('email_verified_at')
            ->sortBy([
                ['last_name', 'asc'],
                ['first_name', 'asc'],
            ])
            ->values()
            ->toArray();

        view()->share('subscribers', $data);

        $selectedDay = !is_null($day) ? "-day_{$day}-" : "";
        $fileName = 'subscribers_' . $selectedDay . date('Y-m-d-His') . '.pdf';

        return PDF::loadView('pdf.subscribers', $data)
            ->setPaper('a4', 'landscape')
            ->download($fileName);
    }

    public function confirmation(Request $request, $userId): Response
    {
        $user = User::query()
            ->where('users.slug', '=', $userId)
            ->firstOrFail();
        $profile = $user->profile()->first();

        return Inertia::render('RegistrationConfirmation', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function switchLocale(Request $request, $locale): RedirectResponse
    {
        if (in_array(strtolower($locale), config('translatable.locales'), true)) {
            app()->setLocale($locale);
            session()->put('locale', $locale);
        }

        return redirect()->back();
    }

    public function terms(): Response
    {
        $termsFile = Jetstream::localizedMarkdownPath('terms.md');

        return Inertia::render('TermsOfService', [
            'termsTitle' => 'Terms of Service',
            'terms' => Str::markdown(file_get_contents($termsFile)),
        ]);
    }

    public function cookies(): Response
    {
        $termsFile = Jetstream::localizedMarkdownPath('cookies.md');

        return Inertia::render('TermsOfService', [
            'termsTitle' => 'Cookie Policy',
            'terms' => Str::markdown(file_get_contents($termsFile)),
        ]);
    }

    private function getPageData(): array
    {
        $days = Day::query()
            ->with(['host'])
            ->get()
            ->keyBy('id');
        $speakers = Person::query()
            ->inRandomOrder()
            ->get();
        $presentationsList = Presentation::query()
            ->with(['speakers', 'moderators'])
            ->get();
        $presentations = $presentationsList
            ->groupBy('day_id');
        $moderators = $presentationsList
            ->groupBy('day_id')
            ->transform(fn($day) => $day->pluck('moderators', 'id')->flatten()->keyBy('id')->values());
        $days->transform(fn($day, $index) => collect($day)->merge(['moderators' => $moderators[$index]->toArray()]));

        return compact(['days', 'speakers', 'presentations']);
    }

    private function getSubscribersList(int $day, int $volunteersOnly): array|Collection
    {
        return User::query()
            ->with('profile')
            ->withWhereHas('profile', static function ($query) use ($day, $volunteersOnly) {
                $query
                    ->when($volunteersOnly, static function ($query) {
                        $query->where('profiles.type', '=', 'volunteer');
                    }, static function ($query) {
                        $query->where('profiles.type', '<>', 'volunteer');
                    })
                    ->when($day, static function ($query, $day) {
                        $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(event_details, '$.days')) LIKE '%{$day}%'");
                    });
            })
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
