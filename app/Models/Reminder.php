<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A reminder email to everyone registered — see Admin\ReminderController. */
class Reminder extends Model
{
    protected $connection = 'wcm_2026';

    protected $fillable = ['subject', 'subject_ro', 'body', 'body_ro'];

    public function sends(): HasMany
    {
        return $this->hasMany(ReminderSend::class);
    }

    /**
     * What a reminder starts from, in the voice of the other emails: the
     * dates and the venue, the programme online, and the note that comes the
     * evening before each day. The office edits it before sending.
     */
    public const STARTER = [
        'subject' => 'Why Culture Matters 2026 starts on 7 October · see you at FABER',
        'subject_ro' => 'Why Culture Matters 2026 începe pe 7 octombrie · ne vedem la FABER',
        'body' => '<p>Dear {name},</p>'
            .'<p>A friendly reminder: <strong>Why Culture Matters 2026</strong> begins on <strong>Wednesday, 7 October</strong>, '
            .'at <strong>FABER</strong>, Splaiul Peneș Curcanul 4–5, Timișoara, and runs until 10 October. '
            .'Registration and the welcome coffee start at <strong>09:15</strong>.</p>'
            .'<p>You registered for <strong>{days}</strong>.</p>'
            .'<p>The full programme, day by day, is at '
            .'<a href="https://whyculturematters.eu/2026/programme">whyculturematters.eu/2026/programme</a>. '
            .'The evening before each day, we will send you a few lines about what to expect.</p>',
        'body_ro' => '<p>Dragă {name},</p>'
            .'<p>Îți reamintim că <strong>Why Culture Matters 2026</strong> începe <strong>miercuri, 7 octombrie</strong>, '
            .'la <strong>FABER</strong>, Splaiul Peneș Curcanul 4–5, Timișoara, și durează până pe 10 octombrie. '
            .'Înregistrarea și cafeaua de bun venit încep la <strong>09:15</strong>.</p>'
            .'<p>Te-ai înscris pentru <strong>{days}</strong>.</p>'
            .'<p>Programul complet, zi cu zi, îl găsești la '
            .'<a href="https://whyculturematters.eu/2026/ro/program">whyculturematters.eu/2026/ro/program</a>. '
            .'În seara dinaintea fiecărei zile îți vom trimite câteva rânduri despre ce te așteaptă.</p>',
    ];

    /**
     * The one being worked on: the latest, or a fresh one if there is none.
     * One never written in starts from the starter text.
     */
    public static function current(): self
    {
        $reminder = static::latest('id')->first() ?? static::create(self::STARTER);

        if (blank($reminder->subject) && blank(strip_tags((string) $reminder->body))) {
            $reminder->update(self::STARTER);
        }

        return $reminder;
    }
}
