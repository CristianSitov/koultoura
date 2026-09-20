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
  `slug`. A **workshop or guided tour is an "exception"**:
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

## Guardrails & conventions

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
