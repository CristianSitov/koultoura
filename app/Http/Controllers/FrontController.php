<?php

namespace App\Http\Controllers;

use App\Mail\EventRegistrationConfirmation;
use App\Models\Day;
use App\Models\Presentation;
use App\Models\Person;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use MaxMind\Db\Reader\InvalidDatabaseException;

class FrontController extends Controller
{
    /**
     * fallback: return Inertia::render('Splash');
     * @return Response
     */
    public function index(Request $request): Response
    {
        if(!Session::has('locale')) {
            try {
                $geoIpReader = new Reader(resource_path() . '/app/GeoLite2-Country.mmdb');
                $userLocation = $geoIpReader->country($request->ip()); // '2a02:2f09:3419:af00:fd10:4ef8:8c40:d405'
                App::setLocale(strtolower($userLocation->country->isoCode ?? 'en'));
            } catch (AddressNotFoundException | InvalidDatabaseException $e) {
                App::setLocale('en');
            }
        }

        $days = Day::query()
            ->with(['host'])
            ->get()
            ->keyBy('id');
        $speakers = Person::query()
            ->inRandomOrder()
            ->get();
        $presentations = Presentation::query()
            ->with(['speakers', 'moderators'])
            ->get();
        $presentationsByDay = $presentations
            ->groupBy('day_id');
        $moderators = $presentations
            ->groupBy('day_id')
            ->transform(fn ($day) => $day->pluck('moderators', 'id')->flatten()->keyBy('id')->values());
        $days->transform(fn ($day, $index) => collect($day)->merge(['moderators' => $moderators[$index]->toArray()]));

        return Inertia::render('Home', [
            'days' => $days,
            'speakers' => $speakers,
            'presentations' => $presentationsByDay,
            'detected_ip' => json_encode($userLocation ?? '[]', JSON_THROW_ON_ERROR),
        ]);
    }

    public function eventRegistration(Request $request): RedirectResponse
    {
        $input = $request->input();
        $input['name'] = implode(' ', [$input['first_name'], $input['last_name']]);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            'job' => ['required', 'string'],
            'organization' => ['required', 'string'],
            'country' => ['required', 'string'],
            'event_details.*' => ['required', 'numeric', 'in:1,2,3'],
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

    public function subscribersList(Request $request): Response
    {
        return Inertia::render('Tools/Subscribers', [
            'subscribers' => User::query()
                ->with('profile')
                ->orderBy('users.created_at', 'DESC')
                ->get()
        ]);
    }

    public function subscribersListPdf(Request $request): \Illuminate\Http\Response
    {
        $data = User::query()
            ->with('profile')
            ->whereNull('users.email_verified_at')
            ->orderBy('users.name', 'DESC')
            ->get()
            ->toArray();

        view()->share('subscribers', $data);

        return PDF::loadView('pdf.subscribers', $data)
            ->setPaper('a4', 'landscape')
            ->download('subscribers_'.date('Y-m-d-His').'.pdf');
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
}
