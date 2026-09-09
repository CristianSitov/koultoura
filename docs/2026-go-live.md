# Why Culture Matters 2026 — live

**The edition went public on 2026-09-09.** `WCM_2026_PUBLIC=true`, the site is
at `/2026`, Stripe is on live keys, and the first real registration arrived the
same morning. What follows is what was checked on the way, what is still open,
and how to go back if it comes to that.

Reviewed against the server on 2026-09-09.

---

## 0. What is still open

- [ ] **Registration #1 never got its confirmation email.** `sent_count` is 0:
      the email is sent when someone reaches the "check your email" page, and
      that only happens after they contribute or skip. Close the tab on the
      contribute step and nothing is sent at all. Resend it from
      `/dashboard/registrations`, and decide whether the flow should send on
      registering instead — this will keep happening otherwise.
- [ ] **The programme is public and empty.** The switch is on, so visitors see
      four days each reading "coming soon". 0 of 20 sessions are published.
- [ ] **No workshop is bookable** and nothing links to `/2026/sessions/{slug}`.
      The 10th has no way in.
- [ ] **Four supporter marks** — `djc`, `kek`, `cicasp`, `oar-timis` — are still
      on their original canvases and sit at odds with the rest of the row. See
      `docs/2026-partner-logos.md`.
- [ ] **Two partners have no link**: Fundația Culturală Jazz Banat and Centrul
      de Proiecte.
- [ ] **No `sitemap.xml`.** Worth adding now that the site is indexable.
- [ ] **One backoffice account.** If anyone else needs one:
      `php8.2 artisan admin:create <email> <password>`.

## 1. The switch — now pointing at live

Three environment variables decide it. No code change, no deploy: edit `.env`,
clear the config cache, done. **They are currently set to live**, so the recipe
below is mostly a rollback.

Both sets of values are already written into the server's `.env`, one of them
commented out. Flipping is a matter of swapping which — no value to look up, no
line to type from memory.

**Roll back** — if something has to come down in a hurry:

    ssh -p 2221 root@heritageoftimisoara.ro
    cd /var/www/whyculturematters.eu

    # in .env, under "The 2026 edition": comment the LIVE three,
    # uncomment the PREVIEW three. Then:
    php8.2 artisan config:clear

The site returns to its unlisted address and stops being indexed within a
request. Stripe is a separate block in the same file and rolls back the same
way, though a payment taken live stays taken.

**Go live again** — the same swap the other way.

The Stripe block above it is laid out the same way, with an empty LIVE set to
fill in. That one is not just an uncomment: the four live values have to be
created in the Stripe dashboard first (§3).

### What each one does

`WCM_2026_PATH` — the segment the whole edition is served under. Everything is
built from it: the routes, the locale switch, the guest pages, the registration
links, and the addresses inside confirmation emails already sent.

`WCM_2026_PUBLIC` — off, the landing page sends `noindex, nofollow`, the 2026
routes are withheld from the Ziggy table shipped inside `app.js`, and `/` shows
the announcement page. On, all three reverse: the page says `index, follow`,
names a canonical address for the language being read, ships its routes, and `/`
redirects to the landing page.

`WCM_2026_OLD_PATHS` — a comma-separated list of segments the site used to
answer on. Each one keeps answering and forwards whatever follows it, so a
confirmation link posted as `/2026-mulberry/confirm/{token}` lands on
`/2026/confirm/{token}` rather than a 404. **Leave `2026-mulberry` in this list
for as long as any of those emails might still be clicked** — months, not days.

### Checked both ways

Flipped on and off locally before this was written:

| | preview | live |
| --- | --- | --- |
| `/` | announcement page, 200 | 302 → `/2026` |
| `/2026` | 404 | 200 |
| `/2026-mulberry` | 200 | 302 → `/2026` |
| `/2026-mulberry/ro/register` | 200 | 302 → `/2026/ro/register` |
| robots | `noindex, nofollow` | `index, follow` |
| canonical | none | the address for the language read |
| 2026 routes in `app.js` | withheld | shipped |

## 2. Search engines

`WCM_2026_PUBLIC` handles the landing page. The rest stays as it is:

- [ ] **Leave `noindex` on the other five** — `Registration.vue`,
      `RegistrationSubmitted.vue`, `RegistrationConfirmed.vue`, `Contribute.vue`
      and `SessionBooking.vue`. A registration form, a confirmation page and a
      booking form have nothing to offer a search result, and three of them
      carry a token in the URL.
- [ ] **`Support.vue` and `Cookies.vue` carry no `noindex`** and never did.
      Harmless while the address is secret; decide whether they should be
      indexed once it is not.
- [ ] Consider a `sitemap.xml` and a `robots.txt` once the address is public.

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
      `https://whyculturematters.eu/stripe/webhook`, events
      `checkout.session.completed` and `charge.refunded`. The signing secret it gives you is
      `STRIPE_WEBHOOK_SECRET`; without it the endpoint answers 500 and Stripe
      retries into nothing.
- [x] **Branding** is set in live mode: logo, and the primary colour is the
      site's own accent, `#bf1a2c`.
- [x] **Emailed receipts** are on in live mode, for successful payments and for
      refunds. Note these come from Stripe, not from Resend.
- [x] **Customer-facing name** reads "Asociația Prin Banat", and the statement
      descriptor "ASOCIAȚIA PRIN BANAT". (`settings.dashboard.display_name`
      still says prinbanat.ngo, but that is the label on your own dashboard, not
      anything a payer sees.)
- [x] **Support email** `contact@prinbanat.ro` and phone are on the account, so
      the receipt carries a way to reach the association.
- [x] The **statement descriptor** is plain ASCII — card networks do not carry
      diacritics, and a mangled name on a statement invites chargebacks.
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

Backoffice logins are the rows flagged `backoffice` on the 2024 `users` table.
That table is also the 2024 subscriber list, so before this flag every row in it
carrying a password hash was a working login — forty-seven of them. The flag is
enforced by a global scope on `App\Models\Admin`, so it covers the guard's own
credential lookup, not just queries written by hand.

    php8.2 artisan admin:create <email> <password>   # grants access
    php8.2 artisan admin:revoke <email>              # takes it away

- [ ] Check who holds access before the address is public:
      `Admin::pluck('email')`. A signed-in visitor sees draft programme
      sessions, so this is not only a write concern.
- [ ] There is no public sign-up form, deliberately.
- [ ] `/dashboard` is the 2026 backoffice; `/dashboard/2024` is the old one.
- [ ] The account rows still live on the **2024** database whatever URL is being
      visited. Moving them is a schema decision for after the event.

## 10. Notices to the office

Slack carries what happens: a registration, an address confirmed, a
contribution, a refund, a workshop place booked and a workshop filling up, a
sign-in and a failed sign-in, and the failures worth interrupting someone for —
a confirmation email that did not go out, Stripe refusing to open a payment
page, a webhook refused for a bad signature, a payment the webhook missed, and
any 500.

- [x] `SLACK_WEBHOOK_URL` is set. `php8.2 artisan slack:test` proves it.
- [ ] The webhook URL is a secret in the sense that anyone holding it can post
      to the channel. Rotate it in the Slack app if it has been somewhere it
      should not have been.
- [ ] One channel carries both the good news and the alarms. Splitting them is
      a second env var if the mix turns out to be wrong.

## 11. Analytics and cookies

- [ ] **Google Analytics is live** (`G-WYGPJKWNT1`), and fires only when the
      visitor accepts the analytics category. Confirm one real accept produces
      a `googletagmanager.com` request — this has not been watched end to end.
- [x] The consent tables named `prinbanat.ngo` and linked to
      `//prinbanat.ngo/contact/`. Now `whyculturematters.eu` and the
      association's own `https://prinbanat.ro/contact/`.
- [ ] The cookie policy lists `_ga` and says it is set only on consent. Keep
      that true.

## 12. Deploying

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

## 13. Done on the day

- [x] Registered end to end in both languages, paid with a real card, refunded
      it, and confirmed the webhook recorded the contribution on its own —
      which is the only thing that proves the live signing secret.
- [x] The confirmation email arrives from `why-culture-matters@prinbanat.ro`
      and its links point at `/2026`.
- [x] Old `/2026-mulberry/*` addresses redirect, tail and all.
- [x] The announcement page is archived at `/announcement`, noindex.
- [ ] Check the landing page at 393px as well as on a desktop.
- [ ] Watch `storage/logs/laravel.log` over the first days of real sign-ups.
