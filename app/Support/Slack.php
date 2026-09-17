<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Notices to the office — Slack, and WhatsApp for the ones that matter most.
 *
 * Three rules, all of them the same rule: a notification must never be able to
 * hurt the thing it is reporting on.
 *
 * - Unconfigured is silent. No webhook, no attempt, no error — which is what
 *   makes this safe to leave in place on a laptop.
 * - Sent after the response, through `terminating()`, so nobody waits on
 *   Slack to finish registering. There is no queue worker on this server, so a
 *   queued job would never run.
 * - Failure is logged and swallowed. Slack being down is not a reason for a
 *   registration to fail.
 */
class Slack
{
    /** Alerts repeat; the same one within this many seconds is dropped. */
    private const REPEAT_AFTER = 300;

    public static function good(string $title, array $fields = []): void
    {
        self::send('✅', $title, $fields, whatsapp: true);
    }

    // Slack only: the low-signal ones (a sign-in, an address confirmed). A
    // phone should not buzz for these.
    public static function info(string $title, array $fields = []): void
    {
        self::send('•', $title, $fields, whatsapp: false);
    }

    public static function warn(string $title, array $fields = []): void
    {
        self::send('⚠️', $title, $fields, whatsapp: true);
    }

    public static function alert(string $title, array $fields = []): void
    {
        self::send('🚨', $title, $fields, whatsapp: true);
    }

    /**
     * The same as alert(), but silent if this exact alert has just been sent.
     *
     * For the ones that arrive in floods: a 500 in a loop, or a webhook being
     * retried. The first is the useful one; the next hundred are noise that
     * makes the channel worth muting, which is worse than no channel.
     */
    public static function throttled(string $key, string $title, array $fields = []): void
    {
        if (! self::configured() && ! WhatsApp::configured()) {
            return;
        }

        if (! Cache::add('slack:'.md5($key), true, self::REPEAT_AFTER)) {
            return;
        }

        self::send('🚨', $title, $fields, whatsapp: true);
    }

    private static function configured(): bool
    {
        return filled(config('services.slack.webhook'));
    }

    private static function send(string $icon, string $title, array $fields, bool $whatsapp = false): void
    {
        $toWhatsApp = $whatsapp && WhatsApp::configured();

        if (! self::configured() && ! $toWhatsApp) {
            return;
        }

        // Slack's own bold (*x*) reads as bold on WhatsApp too, so one text
        // serves both. Recipients see the same line the channel does.
        $lines = [$icon.' *'.$title.'*'];

        foreach ($fields as $label => $value) {
            if (blank($value)) {
                continue;
            }

            $lines[] = is_int($label) ? '· '.$value : '· *'.$label.':* '.$value;
        }

        $text = implode("\n", $lines);

        if (self::configured()) {
            // After the response: the visitor is already gone by the time this runs.
            app()->terminating(function () use ($text) {
                try {
                    Http::timeout(4)->post(config('services.slack.webhook'), ['text' => $text]);
                } catch (Throwable $e) {
                    Log::warning('Slack notification failed', ['error' => $e->getMessage()]);
                }
            });
        }

        if ($toWhatsApp) {
            WhatsApp::send($text);
        }
    }
}
