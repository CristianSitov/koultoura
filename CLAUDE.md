# CLAUDE.md — koultoura (Why Culture Matters)

Guidance for working in this repo. The **active project is WCM 2026**
(`whyculturematters.eu`). Older editions (2024, 2022) still live here but are
frozen — don't touch them unless asked.

## Stack

- **Laravel** (PHP 8.2) + **Inertia** (`@inertiajs/inertia-vue3` 0.6.0) + **Vue 3**
  `<script setup>`, built with **Vite**. `npm run build` (there is no separate
  typecheck/lint step).
- **Jetstream/Fortify** for the backoffice auth.
- i18n: **laravel-vue-i18n** — `$t('English string')` in templates, `trans()` /
  `translateKind()` in scripts. **English text is the key**; translations live in
  `lang/en.json` + `lang/ro.json`.
- Translatable models: **astrotomic/laravel-translatable**.
- Maps: mapbox-gl (big chunk, expected).

## Databases — read this before any tinker/script

Multiple MySQL schemas, one per edition. Most 2026 models **pin their connection**:

```php
protected $connection = 'wcm_2026';   // Session, Theme, ProgrammeDay,
                                      // SessionBooking, Contribution,
                                      // Registration, Setting, SessionPlace
```

**Gotcha:** `Person` (and its `person_*` tables) is **not pinned** — it resolves
via the default connection. In a script/tinker that needs Person on 2026:

```php
Config::set('database.default', 'wcm_2026'); DB::purge(); DB::reconnect('wcm_2026');
```

Backoffice **admins live in `wcm_2024`** (the `Admin` model is pinned there with a
global backoffice scope; the table is `users`, not `admins`).

Local `.env` maps: `DB_DATABASE_2026=wcm_2026`, `_2024=wcm_2024`, `_2022=wcm_2022`.

## WCM 2026 architecture

- **Controllers:** `Front2026Controller` (the single-page front site + section
  routing), `Front2026RegistrationController` (the main symposium registration),
  `Front2026SessionController` (per-workshop/tour booking pages). Backoffice:
  `app/Http/Controllers/Admin/…` incl. `Overview2026Controller`.
- **Routes:** `routes/web.php`, all under `Front2026Controller::path()` (`2026`).
  Base path + localized section slugs come from the controller
  (`sectionsPattern()`, `sectionAnchor()`); `Resolve2026Locale` middleware keeps
  `/2026/en/...` vs `/2026/ro/...` canonical. Go-live is one switch:
  `WCM_2026_PUBLIC` / `config/wcm.php` — see `docs/2026-go-live.md`.
- **Front page = one Vue page of stacked sections:**
  `resources/js/Sections/2026/*.vue` (Hero, About, Themes, Format, Programme,
  Guests, Partners, Register, Bottom, …). `SectionHead`, `ImageSlot`,
  `SessionModal` are shared pieces; `kinds.js` maps session kinds to labels.
- **Programme model:** `ProgrammeDay` → `sessions` → `speakers` (Person, via
  `person_session`) + `theme` + `moderator`. `Session` has `kind`, `type`,
  `starts_at`, `image`, `school`, `youth`, `published`, `bookable`, `capacity`,
  `slug`, `link`. `link` (one address, both languages) makes a **plain**
  session's title a link — underlined, small arrow, new tab, incl. breaks; it is
  forced to null for workshops/tours, whose card is a button opening the modal. A **workshop or guided tour is an "exception"**:
  `Session::EXCEPTIONS = ['workshop','tour']`, `isException()` — these are
  clickable, carry a `detail` payload (image, subtitle, description, trainer
  photos, booking url) and open `SessionModal`. Plain slots don't.
- **Drafts:** unpublished days/sessions only reach the page for a **signed-in**
  reader (preview), marked `draft`; the public sees published only, or "coming
  soon". The whole programme is gated by `Setting::PROGRAMME_VISIBLE` **or** auth.
- **Registration guardrails:** youth (under-18) workshops require a guardian's
  name/phone + written consent `De acord` (trimmed, case-insensitive). Oversized
  image uploads are caught (`PostTooLargeException` → form error, not a 502) —
  browser-side guard in `resources/js/imageGuard.js` (8 MB), server net in
  `app/Exceptions/Handler.php`.

## Design / styling

- **Front 2026 pages use hand-written CSS, NOT Tailwind:**
  `resources/css/wcm2026.css`. Colours are CSS tokens on `:root` with a dark-mode
  block; use `var(--color-accent)` (red), `--color-text`, `--color-surface`,
  `--color-accent-100`, `--font-heading` (Manrope), and `color-mix(...)` for
  tints. Current aesthetic: **square corners** (no border-radius on chips/boxes),
  editorial type, chips share one shape (school = pink, draft = amber).
- **Rich text** (`Pages/Admin/2026/RichText.vue` + `App\Support\HtmlBio`, used for
  speaker/session descriptions, day briefs, the reminder): paragraphs, bold,
  italic, underline and **links** — the cleaner keeps an `<a>` only with an
  http/https/mailto href and writes it back with `target="_blank" rel="noopener"`.
- **Emails**: the header and footer name is `config('wcm.name')` ("Why Culture Matters",
  `resources/views/vendor/mail/*/message.blade.php`); the sender is
  `MAIL_FROM_NAME` on the server ("Why Culture Matters - No Reply"). Never rename
  `APP_NAME` for this — the session cookie is named after it.
- **Backoffice pages DO use Tailwind** (`resources/js/Pages/Admin/2026/*.vue`).
- Runtime-uploaded images render through `ImageSlot` (shows a grey placeholder
  until a file exists).

## Local development

- **DB:** Docker container `wcm-mysql` on `127.0.0.1:33061`, `root`/`secret`.
  (`su-mysql` on 3306 is unrelated — don't use it.)
- **Preview:** `preview_start {name: "wcm"}` (from `.claude/launch.json`) →
  `php artisan serve` on **:8123**. Never run the dev server via Bash.
- **Mail:** `MAIL_MAILER=log` — sent mail lands in `storage/logs/laravel.log`.
- The app clock can run ~1 day behind the host date (affects dated log filenames;
  content still goes to base `laravel.log`).

### Pull prod data to local (for testing)

```bash
# on prod: dump 2026 (+2024 for backoffice logins), gzip
ssh -p 2221 root@heritageoftimisoara.ro \
  "mysqldump -u whyculturematters -p'<pw>' --single-transaction --no-tablespaces \
   --databases whyculturematters_2026 whyculturematters_2024 | gzip > /tmp/wcm.sql.gz"
scp -P 2221 root@heritageoftimisoara.ro:/tmp/wcm.sql.gz .
# remap prod db names → local, import into the Docker container
gunzip -c wcm.sql.gz | sed -e 's/whyculturematters_2026/wcm_2026/g' \
  -e 's/whyculturematters_2024/wcm_2024/g' | docker exec -i wcm-mysql mysql -uroot -psecret
```

Imported data is **real registrations (PII)** — fine locally, never commit or
expose it. Backoffice login then uses **prod password hashes** (sign in with the
real password; no local reset).

## Deploy

**Run the deploy ON the prod server, not locally.** `Envoy.blade.php` targets
`@servers(['localhost' => '127.0.0.1'])`, so it deploys whatever box it runs on —
run it locally and it tries to build your Mac (fails: no `php8.2`, no `www-data`).

```bash
# 1. ALWAYS check in-flight registrations first (deploy resets + rebuilds):
ssh -p 2221 root@heritageoftimisoara.ro "cd /var/www/whyculturematters.eu && \
  php8.2 artisan tinker --execute='echo App\Models\Registration::where(\"created_at\",\">\",now()->subMinutes(20))->count();'"

# 2. Push first — the deploy does `git reset --hard origin/<branch>`, so only
#    what is on the remote ships:
git push origin main

# 3. Deploy on the server:
ssh -p 2221 root@heritageoftimisoara.ro "cd /var/www/whyculturematters.eu && \
  php8.2 vendor/bin/envoy run deploy --branch=main"
```

The task runs: `git fetch` + `git reset --hard`, `composer install`,
`artisan migrate --force`, `optimize:clear`, `ziggy:generate`, `npm install`,
`npm run build`, `build:prune --days=7` (old Vite chunks are kept a week on
purpose so mid-session pages don't 404), `chown -R www-data`.

**Hosting:** shared VPS `root@heritageoftimisoara.ro:2221`; this site lives at
`/var/www/whyculturematters.eu`; prod DBs are `whyculturematters_2026/_2024/_2022`.

## Reminder email (Registrations → "Reminder email")

- Full-screen window (`Pages/Admin/2026/ReminderEmail.vue`, `Admin\ReminderController`):
  subject + RichText body in EN and RO (RO empty → EN), `{name}` and `{days}`
  (the person's registered days in their language — on a line of its own a
  bulleted list via `daysList()`, inline `daysText()`) replaced per person; Save; test to the signed-in admin (EN/RO, not recorded); send to every
  active registration (confirmed or not) not yet sent; then **Check delivery**.
- `reminders` (one row per round — "Start a new reminder" copies the text and
  starts with nobody sent) and `reminder_sends` (unique reminder+registration,
  `resend_id`, `status`). The id comes from Laravel's Resend transport header
  `X-Resend-Email-ID`; status is Resend's `emails->get($id)->last_event`.
  Locally (`MAIL_MAILER=log`) there is no id: status `logged`, nothing to check.
- Sending **and** checking are one request per person with a 600 ms pause,
  driven by the window — Resend's ~2 req/s limit covers lookups too. Final
  states (`ReminderSend::FINAL`) are not looked up again.
- The Registrations table's **Reminder** column shows the latest round's status.

## Removing a registration

- Registrations → **Remove** (red, with a confirm) soft-deletes (`SoftDeletes`, `deleted_at`): the
  row leaves every count, the entrance list, the CSV and all emails (they all
  query `Registration` normally); nothing is sent. **Restore** under the
  "removed" filter. Payments stay linked; `ReconcileContributions` reads
  `withTrashed()`.
- A removed address that registers again is **registered afresh** (the
  form looks it up `withTrashed()`, the email column is unique).
- Rows whose name matches another registration (case, accents, word order
  aside) are tagged **possible duplicate**.

## Entrance list (Registrations → "Entrance list")

- Full-screen list of **every** registration (confirmed or not) — with day tabs
  (All · 7 · 8 · 9 Oct) on top, which filter the list and the print — sorted by
  **last name** (then first), and a row re-sorts into place once a corrected or
  swapped name is saved — a swap waits for its row’s **Save** button (`Pages/Admin/2026/EntranceList.vue`, data from
  `GET /dashboard/registrations/entrance`).
- First/last name are `registrations.first_name/last_name`, **null until the
  office edits a row**; until then `Registration::nameParts()` reads them off
  `name` (last word = surname). Saving a row (on blur, or ⇄ swap) writes both
  and **rebuilds `name`**, which every email and the CSV use.
- **Print / PDF** uses the browser's dialog, no library: a print-only copy is
  teleported to `<body>`; `body.entrance-printing` hides the rest. N per page
  (5–60) sets `--rows`; row height and type size are derived from it, and each
  cell is capped to its row so a page never spills. Printed columns are fixed
  by the user: **Last name · First name · Organization · Signature** (empty).
  The editor shows last name before first name too; days are on screen only.
  "Blank pages" (0–20) adds empty pages with the same columns for late
  arrivals, after the list or — "Only blank" — on their own.
- **Speakers are on every day's list** (published `Person`s, institution as
  organization, marked "Speaker"), read-only — their name is the site's, edited in
  Speakers. One whose name matches a registration is left out (already listed).

## Workshop & tour booking confirmations

- **Booking sends nothing by default.** Each workshop has its own switch,
  `sessions.auto_confirm` (Workshops → "Send booking confirmations
  automatically", off by default). On: every new public booking is emailed
  `App\Mail\BookingConfirmed` — always via `BookingConfirmed::sendTo()`, which
  records `session_bookings.confirmation_sent_at/_count`.
- **The email asks**: its link opens the booking page
  (`/2026/{ro/}sessions/{slug}/booked/{token}`), which shows **Confirm my place**
  (`confirmed_at`) / **Release my place** (`cancelled_at`, Slack notice) —
  only once the person has been asked; with the switch off the page is as before.
- **Workshops screen:** per attendee *Confirmed* / *Asked {date}* / *Not asked*
  and "Send confirmation" for anyone unconfirmed; with the switch on, "Send
  confirmations to users who have not confirmed (N)" — everyone unconfirmed,
  already-asked included (that is the reminder), one request per person.
  Nobody is released automatically.
- **Public forms check the email's domain exists** (`email:rfc,dns`) — a typo
  like `datcomp.rp` is refused with "Please check your email address".

## Day pages & the eve-of-day email (participants)

- **Day page:** `/2026/programme/7-oct`, `/2026/ro/program/7-oct`
  (`Front2026Controller::day`, `Pages/2026/Day.vue`). The slug comes from the
  **date** (`Front2026Controller::daySlug()`), never the day's name. Content:
  day/date, theme, moderator, the day's brief, its sessions, a link back to the
  day in the full programme. Same visibility rules as the landing programme.
- **Brief:** `programme_day_translations.description` — "About this day" in
  Programme → Edit day (RichText, cleaned by `HtmlBio`; RO empty → EN).
- **Email:** `App\Mail\DayBrief` (`emails/day-brief`), sent from Programme →
  "Email the day before" (`Admin\DayBriefController`). Recipients: **every**
  registration whose `days` holds the day's number — confirmed or not, by the
  user's choice — once per lowercased email, in the registration's `locale`.
  Day number = place in programme order; `Registration::DAYS` (1–3) are the
  registrable days, so the workshop Saturday (day 4) has no list — workshop/
  tour booking reminders are a planned follow-up (`SessionBooking`).
- **Sending is one email per request, driven by the screen** (600 ms pause),
  never a server loop (Resend ~2/s, PHP 30 s). Each send is recorded in
  `day_brief_sends` (unique day+email), so a run resumes and never doubles.
  "Send a test to yourself" goes to the signed-in admin and is not recorded.

## Internal agenda (speakers, guests)

One page behind **one secret link**, which the office sends from its own mail.
There is no send list, no per-person link and no speaker email in the app — an
earlier version had all three and they were removed on purpose.

- **The page:** `/2026/agenda/{token}` and `/2026/{locale}/agenda/{token}`
  (`Front2026AgendaController`, `Pages/2026/Agenda.vue`). No login; the token is
  compared to `Setting::AGENDA_TOKEN` (a row in `settings`, made on first use),
  a wrong one is a 404. Sent `X-Robots-Tag: noindex`. It has the normal EN/RO
  switch.
- **A day is a few boxes by the clock.** The public programme is **one box per
  day** — never its sessions — running from the first published session's start
  to the last one's end (a session with no end counts as an hour, so a last
  talk at 18:00 gives 19:00). The office can override either hour and writes a
  **note** on the box (when to arrive, where speakers eat): `programme_days.
  agenda_starts_at / agenda_ends_at / agenda_note / agenda_note_ro`. The box
  links to that day of the public programme in a new tab
  (`/2026/programme#day-09`; `Landing.vue` scrolls to the `#day-NN` anchor).
- **Events:** `agenda_events` (own `date`, start/end, title/location/description
  with optional `_ro`). They may fall during the programme's hours (a lunch, a
  press call) — **no overlap check**, by the user's choice; they are listed by
  the clock beside the box. An event has its own date because the agenda also
  covers **the day before and the day after** the symposium —
  `Agenda::dates()` is the one place that range is set. Empty days are not drawn.
- **One builder:** `App\Support\Agenda::days($locale)` feeds both the page and
  the backoffice preview. `Agenda::hours()` is covered by
  `tests/Unit/AgendaHoursTest.php` (no database).
- **Links & maps:** web addresses typed in a note, location or description
  render as links (`resources/js/agendaText.js`, built as Vue parts — never
  `v-html`; check: `node resources/js/agendaText.check.mjs`). A map link
  (Google/Apple Maps, OpenStreetMap, Waze) becomes the event's place: the
  location line opens it, and the link is taken out of the description. A
  Romanian text without a map uses the English one's. The programme box is a
  block with one stretched link (`.wcm26-agenda-more::after`), so links in its
  note sit above it and open themselves.
- **Backoffice:** `/dashboard/agenda` (`Admin\AgendaController`,
  `Pages/Admin/2026/Agenda.vue`) — the link (copy, open, **Reset link**, which
  kills the old address at once), each day's programme box (note + hours),
  events CRUD, and a framed preview in either language.

The root template takes `<html lang>` (which the client reads to pick its
translations) from the page's own `locale` prop before the session — that is
what lets the preview show Romanian to an office reading in English.

## Guardrails & conventions

- **Edit windows keep unsaved work** in the browser (`resources/js/formDraft.js`
  + `Components/DraftNotice.vue`): `useDraft(form, name)`, `start(id)` after the
  form is filled, `finish()` in `onSuccess`. Used by every backoffice modal
  (Agenda, Programme, Workshops attendees) — use it in new ones too.
- **Pop-ups close on a backdrop click only if the press started there**
  (`resources/js/backdrop.js`, built once in setup, `v-on="xBackdrop"`) —
  never `@click.self`, which closes the window when a text selection is let go
  past its edge. The Agenda's two edit windows are full screen instead.

- **Runtime uploads are gitignored** — `/public/assets/2026/sessions/*` and
  `/public/assets/2026/guests/*` (keep the `.gitkeep`s). These live only on prod.
  **Never `git add` a session/guest image**; a demo image once leaked to prod
  this way.
- **Don't reformat `lang/en.json` / `lang/ro.json`.** They are in insertion
  order, **not sorted** — edit with minimal, one-key diffs (a sort reorders 650
  lines). New string: English text is the key in both files, RO gets the
  translation.
- Match the surrounding code: front CSS is bespoke and token-based, backoffice is
  Tailwind — don't cross them.
- Verify front changes in the preview (see `.claude/launch.json` "wcm"); the
  page uses a smooth-scroll that can wedge programmatic `scrollTo` — drive it with
  wheel `scroll` actions or check the DOM instead of fighting screenshots.

More background: `docs/2026-go-live.md`, `docs/2026-og-image.md`,
`docs/2026-partner-logos.md`, `docs/diagrams/`.
