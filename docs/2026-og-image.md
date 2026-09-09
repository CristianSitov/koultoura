# The sharing card

Written 2026-09-09, after the Facebook debugger showed the 2026 site sharing as
a 2024 blurb with no picture at all.

## What was wrong

Three separate faults in five lines of `resources/views/app.blade.php`.

**No image.** The tag was built by gluing a path onto `APP_URL`:

    content="{{ env('APP_URL') }}assets/images/event_banner_2024.jpg"

That needs `APP_URL` to end in a slash, which nothing guarantees and
`.env.example` does not do — without one the address comes out as
`https://whyculturematters.euassets/images/…`, which no scraper can fetch. So
there was no thumbnail: not a wrong picture, no picture. `env()` in a view is
the same bet twice over, since it reads nothing at all once the config is
cached.

**A description from the wrong edition.** It was `__('Event')` — the 2024
copy, describing the *second* edition and the Timișoara 2023 Capital of Culture
title, neither of which is what October 2026 is about.

**Markup inside a meta attribute.** That string carries `<span class='font-bold'>`
around its emphasised phrases, printed raw with `{!! !!}`. A scraper strips the
tags and keeps the text between them — except the words *inside* the spans went
with the tags, which is how Facebook came to show

> The second edition of the addresses two main topics…

The subject of the sentence had been eaten. Escaped plain text now, in both
languages, under `og.description`.

## The card

`public/assets/images/og/wcm-2026.png`, 1200 × 630, ~92 KB.

It is a real screenshot, not a drawing: `resources/og/wcm-2026.html` loads
`resources/css/wcm2026.css` — the site's own stylesheet — and
`resources/og/render.mjs` opens it in Chromium and photographs the `.og`
element. The red, the Manrope, the rules and the flying lines are the ones the
site serves, so the card cannot drift from the page by being maintained
separately.

What it shows is the hero: the wordmark, the eyebrow, one line of the hero's
typing sequence held still, and the dates/venue/entry strip.

Two deliberate departures from the page:

- **The line is the third, not the fourth.** The sequence rests on "why culture
  matters", but the wordmark at the top of the card already says that. "what we
  / choose to protect" is where the sequence is heading anyway, and it breaks
  on the seam the hero types on — lead in the ink, tail in the accent — rather
  than wherever 1056px happens to run out.
- **The type is a size up.** A feed shows this at half its width, where the
  page's 12px labels would arrive as six.

## Re-rendering it

    make og                     # or: node resources/og/render.mjs

Needs a Chromium and playwright; both are usually already installed, and the
script finds playwright globally if the project has no `node_modules`. It is
deliberately not a dependency in `package.json` — this makes an image, it does
not build the site. Every `*.html` in `resources/og/` renders to a PNG of the
same name, and the script refuses to write one whose frame is not exactly
1200 × 630.

Re-render after changing the dates, the venue, the entry terms or the wording,
and commit the PNG: it is served as a static file, not built.

## After deploying a new card

Facebook and LinkedIn cache aggressively, and the address has not changed, so
they will keep serving the old scrape — for the old one, an error. Push the new
one through:

- <https://developers.facebook.com/tools/debug/> — Scrape Again
- <https://www.linkedin.com/post-inspector/>

## Still open

- The card is in English only. `og:description` and `og:image:alt` follow the
  page's language; the picture does not.
- One card serves every page. `og:url` is now the page you are actually on, but
  the picture and the words are the 2026 edition's on the 2022 and 2024 archive
  pages too.
- `public/assets/images/event_banner_2024.jpg` is no longer referenced by
  anything. Left in place — it is the 2024 edition's own banner.
