<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/*
 * Someone the internal agenda is sent to. Either a speaker — the row is linked
 * to them and mirrors their name, address and language — or a guest or one of
 * the team, typed in by hand.
 */
class AgendaRecipient extends Model
{
    protected $connection = 'wcm_2026';

    protected $fillable = [
        'person_id', 'name', 'email', 'locale', 'token', 'last_sent_at', 'sent_count', 'sent_email',
    ];

    protected $casts = [
        'last_sent_at' => 'datetime',
        'sent_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $recipient) {
            $recipient->token ??= Str::random(48);
        });
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /** Sent before, but to an address they no longer use. */
    public function addressChanged(): bool
    {
        return $this->sent_count > 0 && $this->sent_email !== $this->email;
    }

    /** Has not had the programme at the address on file. */
    public function isPending(): bool
    {
        return $this->sent_count === 0 || $this->addressChanged();
    }

    /*
     * Bring the list in line with the speakers: everyone with an address is on
     * it, under the address and language they have now. Run whenever the list
     * is about to be shown, so it cannot be out of date when someone presses
     * send.
     *
     * A row keeps its token through all of this — a speaker's link does not
     * change because their address did.
     */
    public static function syncSpeakers(): void
    {
        $people = Person::whereNotNull('email')->where('email', '!=', '')->get();

        foreach ($people as $person) {
            $recipient = static::where('person_id', $person->id)->first()
                // Typed in by hand before they had an address as a speaker:
                // that row is theirs, with its link and what was sent to it.
                ?? static::whereNull('person_id')->where('email', $person->email)->first()
                ?? new static();

            // Any other hand-typed row with this address is the same inbox; one
            // address is written to once.
            static::whereNull('person_id')
                ->where('email', $person->email)
                ->when($recipient->exists, fn ($q) => $q->whereKeyNot($recipient->id))
                ->delete();

            $recipient->fill([
                'person_id' => $person->id,
                'name' => $person->full_name,
                'email' => $person->email,
                'locale' => $person->email_locale ?: 'en',
            ])->save();
        }

        // A speaker whose address was taken away is no longer written to.
        static::whereNotNull('person_id')
            ->whereNotIn('person_id', $people->pluck('id'))
            ->delete();
    }
}
