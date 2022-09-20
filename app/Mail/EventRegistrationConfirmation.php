<?php

namespace App\Mail;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventRegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $profile;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, Profile $profile)
    {
        $this->user = $user;
        $this->profile = $profile;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject(__('Why Culture Matters? Internation Symposium'))
            ->markdown('emails.event_registration_confirmation')
            ->with('user', $this->user->toArray())
            ->with('profile', $this->profile->toArray());
    }
}
