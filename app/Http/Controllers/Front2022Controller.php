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

class Front2022Controller extends Controller
{
    private DashboardController $dashboardController;

    public function __construct()
    {
        $this->dashboardController = new DashboardController($this);
    }

    /**
     * fallback: return Inertia::render('Splash');
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('2022/Home', $this->getPageData());
    }

    public function schedule(): Response
    {
        return Inertia::render('2022/Schedule', $this->getPageData());
    }

    public function registration()
    {
        auth()->logout();

        return redirect('/register');
    }

    public function dashboard(Request $request): Response
    {
        return $this->dashboardController->dashboard($request);
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
}
