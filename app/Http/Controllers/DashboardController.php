<?php

namespace App\Http\Controllers;

use App\Mail\EventRegistrationConfirmation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController
{
    public function dashboard(Request $request): Response
    {
        return Inertia::render('2024/Dashboard');
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

    public function subscribersList(Request $request, int $day = 0, int $volunteers = 0): Response
    {
        return Inertia::render('Tools/Subscribers', [
            'day' => $day,
            'volunteers' => $volunteers,
            'subscribers' => $this->getSubscribersList($day, $volunteers),
        ]);
    }

    public function reconfirmUser(Request $request, int $userId): \Illuminate\Http\RedirectResponse
    {
        $user = User::query()->find($userId);

        Mail::to($user)
            ->bcc(config('mail.from.address'))
            ->send(new EventRegistrationConfirmation($user->load('profile')));

        return Redirect::route('dashboard_subscribers');
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
