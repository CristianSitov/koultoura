<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The same office notices, on WhatsApp, through CallMeBot.
 *
 * WhatsApp has no channel webhook the way Slack does, so this sends to a short
 * list of phones instead. CallMeBot is the relay: each person messages the bot
 * once to get an apikey, and after that a plain GET delivers a line to them.
 *
 * It follows the same three rules as Slack: unconfigured is silent (empty list,
 * no attempt), sent after the response through terminating() so nobody waits on
 * it, and every failure is logged and swallowed — a notice must never be able
 * to hurt the thing it reports on. Only the higher-signal levels reach here
 * (see App\Support\Slack); logins and the like stay in Slack alone.
 *
 * ponytail: unofficial relay with a per-number rate limit of roughly one
 * message every few seconds. Fine for this volume; move to the WhatsApp Cloud
 * API or Twilio if it ever needs delivery guarantees or templates.
 */
class WhatsApp
{
    /**
     * Recipients as "phone:apikey" pairs, comma-separated in one env var.
     * Phone numbers hold no colon and apikeys are digits, so the split is safe.
     *
     * @return array<int, array{phone: string, apikey: string}>
     */
    public static function recipients(): array
    {
        $raw = (string) config('services.whatsapp.callmebot');

        $out = [];

        foreach (array_filter(array_map('trim', explode(',', $raw))) as $pair) {
            [$phone, $apikey] = array_pad(explode(':', $pair, 2), 2, '');
            $phone = trim($phone);
            $apikey = trim($apikey);

            if ($phone !== '' && $apikey !== '') {
                $out[] = ['phone' => $phone, 'apikey' => $apikey];
            }
        }

        return $out;
    }

    public static function configured(): bool
    {
        return self::recipients() !== [];
    }

    /** Queue a line to every recipient, sent once the response is on its way out. */
    public static function send(string $text): void
    {
        $recipients = self::recipients();

        if ($recipients === []) {
            return;
        }

        app()->terminating(function () use ($recipients, $text) {
            foreach ($recipients as $to) {
                self::deliver($to, $text);
            }
        });
    }

    /** Send now, and say per recipient whether it left — for whatsapp:test. */
    public static function test(string $text): array
    {
        $results = [];

        foreach (self::recipients() as $to) {
            $results[$to['phone']] = self::deliver($to, $text);
        }

        return $results;
    }

    /** @param array{phone: string, apikey: string} $to */
    private static function deliver(array $to, string $text): bool
    {
        try {
            $response = Http::timeout(6)->get('https://api.callmebot.com/whatsapp.php', [
                'phone' => $to['phone'],
                'apikey' => $to['apikey'],
                'text' => $text,
            ]);

            if ($response->failed()) {
                Log::warning('WhatsApp notification rejected', [
                    'phone' => $to['phone'],
                    'status' => $response->status(),
                ]);
            }

            return $response->successful();
        } catch (Throwable $e) {
            Log::warning('WhatsApp notification failed', [
                'phone' => $to['phone'],
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
