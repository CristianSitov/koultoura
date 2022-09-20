<?php

namespace App\Http\Controllers;

use App\Models\Day;
use App\Models\Presentation;
use App\Models\Person;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Contracts\RegisterResponse;

class FrontController extends Controller
{
    public function splash()
    {
//        if (Carbon::now()->lt(Carbon::parse('2022-09-20 18:00:00'))) {
            return Inertia::render('Splash');
//        }
    }

    public function index(): Response
    {
        $translation = array_values(array_diff(config('translatable.locales'), [app()->getLocale()]))[0];

        $days = Day::query()
            ->with(['host'])
            ->get()
            ->keyBy('id');
        $speakers = Person::query()
            ->selectRaw('people.*')
            ->leftJoin('person_presentation', 'person_presentation.person_id', '=', 'people.id')
            ->whereNull('person_presentation.id')
            ->get();
        $presentations = Presentation::with([
            'speakers',
            'moderators',
        ])
            ->get()
            ->groupBy('day_id');

        return Inertia::render('Home', [
            'days' => $days,
            'speakers' => $speakers,
            'presentations' => $presentations,
            'translation' => $translation,
        ]);
    }

    public function locale($language = 'en'): RedirectResponse
    {
        if (in_array($language, config('translatable.locales'), true)) {
            app()->setLocale($language);
            session()->put('locale', $language);
        }

        return redirect()->back();
    }

    public function registration()
    {
        auth()->logout();
        return redirect('/register');
    }

    public function eventRegistration(Request $request)
    {
        $input = $request->input();
        $input['name'] = implode(' ', [$input['first_name'], $input['last_name']]);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
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
            'email' => $input['email'],
            'password' => Hash::make(Str::uuid()),
        ]);

        Profile::create([
            'user_id' => $user->id,
            'phone' => $input['phone'],
            'job' => $input['job'],
            'organization' => $input['organization'],
            'country' => $input['country'],
            'event_details' => json_encode(['days' => $input['event_details']], JSON_THROW_ON_ERROR),
        ]);

        return app(RegisterResponse::class);
    }

    public function confirmation(Request $request, $userId): Response
    {
        $user = User::query()
            ->where('users.slug', '=', $userId)
            ->firstOrFail();
        $profile = $user->profile()->first();

        return Inertia::render('Dashboard', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }
}
