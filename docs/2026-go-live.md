# Why Culture Matters 2026 — going live

Everything the 2026 site is currently holding back, and what has to change when
it stops being an unlisted preview and becomes the public site. Written
2026-09-08; tick items off in the repo rather than in someone's head.

The site is already deployed and working at an address nobody is given. Going
live is mostly *removing* the things that keep it quiet, plus the Stripe
switch, which is the only part that touches money.

---

## 1. The address

The landing page lives behind an unguessable segment so that a crawler walking
the obvious years finds nothing.

- [ ] **`Front2026Controller::PATH`** — `'2026-mulberry'` → `'2026'`.
      One constant; the routes, the locale switch, the guest URLs, the
      registration links and the emails all read it.
- [ ] **`routes/web.php`** — the announcement page at `/` becomes a redirect to
      the landing page. The note at the top of the file has the exact line.
- [ ] **Keep the old prefix alive for a few months.** Every confirmation email
      already sent carries `/2026-mulberry/confirm/{token}`. Add
      `Route::redirect('/2026-mulberry/{rest?}', ...)` or those links die.
- [ ] **`config/ziggy.php`** — drop `'except' => ['2026.*']`. It exists so the
      secret path is not shipped into `app.js` on every page; once the address
      is public it is only getting in the way.

## 2. Search engines

Six pages send `noindex, nofollow` while the site is unlisted:

`Landing.vue`, `Registration.vue`, `RegistrationSubmitted.vue`,
`RegistrationConfirmed.vue`, `Contribute.vue`, `SessionBooking.vue`

- [ ] Remove it from **`Landing.vue`** — that page wants to be found.
- [ ] **Leave it on the other five.** A registration form, a confirmation page
      and a booking form have nothing to offer a search result, and the last
      three carry a token in the URL.

## 3. Stripe — the only part that handles money

**Production currently has no Stripe keys at all.** The contribution step
detects this and skips itself: someone registering goes straight from the form
to "check your email", and no money can be taken. That is the correct
behaviour for a preview, and it means going live is the first time real cards
are involved.

- [ ] Create the contribution **price in live mode**. A test-mode price id does
      not work with live keys, and prices are immutable — a new amount means a
      new price.
- [ ] Put the four live values in the server's `.env`:
      `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_PRICE_ID`, `STRIPE_WEBHOOK_SECRET`.
- [ ] Register the **webhook endpoint in live mode**:
      `https://whyculturematters.eu/stripe/webhook`, event
      `checkout.session.completed`. The signing secret it gives you is
      `STRIPE_WEBHOOK_SECRET`; without it the endpoint answers 500 and Stripe
      retries into nothing.
- [ ] Set **branding** (logo, colours) in the live dashboard. Test-mode
      branding does not carry over.
- [ ] Turn on **emailed receipts** for successful payments, in live mode.
- [ ] Make one **real payment of a few lei**, refund it, and check that
      `make contributions` shows the row. Then run
      `php artisan contributions:reconcile` — it should say nothing is missing.

The webhook is the only thing that records a contribution; a browser returning
from Stripe proves nothing. If the endpoint is misconfigured, money arrives and
the table stays empty — which is exactly what `contributions:reconcile` is for.

## 4. The programme

- [ ] Replace the **placeholder schedule**. The guests are real; what they are
      down to speak about is invented. Edit at `/dashboard/programme`.
- [ ] **Publish** each day and each session — both carry their own flag, and
      nothing unpublished reaches the public page.
- [ ] Flip the **"Show the programme"** switch at the top of
      `/dashboard/programme`. Until it is on, the section is not on the site at
      all and the menu has no Programme entry.
- [ ] Decide about the **"Draft · subject to change"** marker in
      `Programme.vue` — it should probably go once the schedule is settled.

## 5. Capped sessions

The eight Heritage School workshops are the ones that fill up.

- [ ] For each: tick **"Places are limited"**, set the number of places, check
      the address it generates. Each gets its own form at
      `/2026/sessions/{slug}`; the day registration does not cover it.
- [ ] Bookings appear at `/dashboard/bookings`, where a place can be released
      back to the pool.
- [ ] **No email is sent when someone books a workshop.** They see a
      confirmation page and the office sees the row. Decide whether that is
      good enough before the workshops open.

## 6. Content still outstanding

- [ ] **Partner logos**: Oradea Heritage and Fundația Culturală Jazz Banat are
      still shown as names rather than marks.
- [ ] **FAINA's logo** is light yellow; the partner strip greyscales
      everything, which leaves it washed out against the page. Either a darker
      variant, or exempt that one file from the greyscale.
- [ ] **Guests** are applied from `Database\Seeders\Guests2026Seeder` with
      `php artisan guests:sync`. Adding one is a row there plus their portrait
      committed under `public/assets/2026/guests`.

## 7. Test data to clear out

- [ ] `wcm_2026.registrations` holds test sign-ups.
- [ ] `wcm_2026.contributions` holds test-mode Stripe rows (RON amounts that
      were never real money).
- [ ] `make registrations-reset` clears registrations and the dev inbox but
      **keeps contributions on purpose** — payment records are not scratch data.
      Clear those by hand, and only in test mode.

## 8. Accounts and access

- [ ] Backoffice logins are made with `php artisan admin:create <email>
      <password>`. There is no public sign-up form, deliberately.
- [ ] The account rows live in the **2024** database, whatever URL is being
      visited — see `App\Models\Admin`. Changing that is a schema decision for
      after the event.
- [ ] `/dashboard` is the 2026 backoffice; `/dashboard/2024` is the old one.

## 9. Deploying

    ssh -p 2221 root@heritageoftimisoara.ro
    cd /var/www/whyculturematters.eu && php8.2 vendor/bin/envoy run deploy --branch=main

That resets the working tree to `origin/main`, installs, migrates, regenerates
Ziggy and rebuilds the assets.

- [ ] Locally, run migrations **by path**, not bare:
      `php artisan migrate --path=database/migrations/<one file>.php`.
      Local databases came from server dumps and their ledgers do not record
      several migrations whose tables already exist, so a bare `migrate` tries
      to re-create them. Production's ledger is consistent and migrates fine.

## 10. Worth a look before announcing

- [ ] Register once with a real address, end to end, in both languages.
- [ ] Confirm the email arrives from `why-culture-matters@prinbanat.ro` and
      that its links point at the new address.
- [ ] Check the landing page at 393px as well as on a desktop.
- [ ] Both languages: `/2026` and `/2026/ro`.
