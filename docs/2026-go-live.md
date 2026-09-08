# Why Culture Matters 2026 — going live

Everything the 2026 site is holding back, and what has to change when it stops
being an unlisted preview and becomes the public site. Written 2026-09-08,
reviewed the same evening against what is actually on the server; tick items off
in the repo rather than in someone's head.

The site is deployed and working at an address nobody is given. Going live is
mostly *removing* the things that keep it quiet, plus the Stripe switch, which
is the only part that touches money.

**Verified on the server as of this review:** `APP_ENV=production`,
`APP_DEBUG=false`, `APP_URL=https://whyculturematters.eu/`, mail going out
through Resend, Stripe in test mode, no registrations, no contributions, no
bookings.

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
- [ ] **`Support.vue` and `Cookies.vue` carry no `noindex`** and never did.
      Harmless while the address is secret; decide whether they should be
      indexed once it is not.

## 3. Stripe — the only part that handles money

**Production is in test mode.** `STRIPE_KEY` is a `pk_test_` key,
`STRIPE_SECRET` an `sk_test_`, and `STRIPE_PRICE_ID` and
`STRIPE_WEBHOOK_SECRET` are both set and working. Someone can complete the
contribution step today with a test card and no money moves. Going live is a
swap of all four, not a first configuration.

- [ ] Create the contribution **price in live mode**. A test-mode price id does
      not work with live keys, and prices are immutable — a new amount means a
      new price.
- [ ] Replace all four values in the server's `.env`:
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

## 4. Email — working

`MAIL_MAILER=resend`, `RESEND_KEY` is set and accepted, and mail goes out from
`why-culture-matters@prinbanat.ro`. The key that was rejected has been replaced.

- [ ] Nothing to do before launch. Send yourself one real registration as part
      of the final checks in §11.
- [ ] A failed send is caught and logged (`2026 confirmation email failed` in
      `storage/logs/laravel.log`) rather than taking the registration down with
      it — so it would fail quietly. Worth a look at that log after the first
      day of real sign-ups.

## 5. The programme

**The section is already public.** The "Show the programme" switch is on, so
anyone with the address sees the four days — each reading "coming soon",
because every session is a draft. That is a reasonable holding state, but it is
live, not hidden.

- [ ] Replace the **placeholder schedule**. The guests are real; what they are
      down to speak about is invented. Edit at `/dashboard/programme`.
- [ ] **Publish** each session as it is settled. Signed in, drafts are visible
      on the public page on a yellow band; signed out they are not sent at all,
      and a day with nothing published says "coming soon".
- [ ] Decide about the **"Draft · subject to change"** marker in
      `Programme.vue` — it should probably go once the schedule is settled.
- [ ] The **Heritage School** runs on the 7th, 9th and 10th
      (`ProgrammeDay::SCHOOL_DAYS`). The backoffice refuses a School session on
      the 8th.

## 6. Capped sessions and the workshop day

Registration covers **7–9 October only**. The 10th is the Heritage School's
workshop day, and those are booked one at a time through their own forms.

- [ ] **No workshop is bookable yet** — all 20 sessions have `bookable` off.
      For each of the eight: tick **"Places are limited"**, set the number of
      places, check the address it generates. Each gets its own form at
      `/2026/sessions/{slug}`.
- [ ] **Nothing links to those forms yet.** They are reachable only from a
      published programme session. Decide how someone finds them.
- [ ] Bookings appear at `/dashboard/bookings`, where a place can be released
      back to the pool.
- [ ] **No email is sent when someone books a workshop.** They see a
      confirmation page and the office sees the row. Decide whether that is
      good enough before the workshops open.

## 7. Content still outstanding

- [ ] **Partner marks**: `djc`, `kek`, `cicasp` and `oar-timis` are still on
      their original canvases and sit at odds with the rest of the row. See
      `docs/2026-partner-logos.md` for the sizes.
- [ ] **`arhabito.png`** is in the assets folder but on no row and with no link.
      It is not in the partner list in the source document.
- [ ] **Fundația Culturală Jazz Banat** is the one mark with no link; 15 of the
      16 are linked.
- [ ] **Social links in the footer** are still `instagram.com` and
      `facebook.com` — placeholders from the handoff.
- [ ] **Guests** are applied from `Database\Seeders\Guests2026Seeder` with
      `php artisan guests:sync`, which is **not part of the deploy** — it has to
      be run on the server by hand after any guest change.

## 8. Test data

- [ ] Nothing to clear: registrations, contributions and bookings are all empty
      on production.
- [ ] `make registrations-reset` clears registrations and the dev inbox but
      **keeps contributions on purpose** — payment records are not scratch data.

## 9. Accounts and access

- [ ] **47 accounts can sign into the backoffice.** `App\Models\Admin` reads
      the `users` table on the **2024** database, which is also the 2024
      subscriber list, so every row there with a password is a login. Worth
      auditing before the address is public; a signed-in visitor now also sees
      draft programme sessions.
- [ ] Backoffice logins are made with `php artisan admin:create <email>
      <password>`. There is no public sign-up form, deliberately.
- [ ] `/dashboard` is the 2026 backoffice; `/dashboard/2024` is the old one.

## 10. Analytics and cookies

- [ ] **Google Analytics is live** (`G-WYGPJKWNT1`), and fires only when the
      visitor accepts the analytics category. Confirm one real accept produces
      a `googletagmanager.com` request — this has not been watched end to end.
- [ ] The consent tables in `resources/js/consent.js` name the domain
      **`prinbanat.ngo`** and link to `//prinbanat.ngo/contact/`. Both are wrong
      for this site.
- [ ] The cookie policy lists `_ga` and says it is set only on consent. Keep
      that true.

## 11. Deploying

    ssh -p 2221 root@heritageoftimisoara.ro
    cd /var/www/whyculturematters.eu && php8.2 vendor/bin/envoy run deploy --branch=main

That resets the working tree to `origin/main`, installs, migrates, regenerates
Ziggy and rebuilds the assets. It does **not** run `guests:sync`, and it does
not touch programme or settings data.

- [ ] Locally, run migrations **by path**, not bare:
      `php artisan migrate --path=database/migrations/<one file>.php`.
      Local databases came from server dumps and their ledgers do not record
      several migrations whose tables already exist, so a bare `migrate` tries
      to re-create them. Production's ledger is consistent and migrates fine.

## 12. Worth a look before announcing

- [ ] Register once with a real address, end to end, in both languages.
- [ ] Confirm the email arrives from `why-culture-matters@prinbanat.ro` and
      that its links point at the new address.
- [ ] Check the landing page at 393px as well as on a desktop.
- [ ] Both languages: `/2026` and `/2026/ro`.
- [ ] The menu, on every page — it is no longer landing-page only.
