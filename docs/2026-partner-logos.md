# Partner logos — sizing them so they read as one set

Written 2026-09-08, from the marks on the live page. The point of this note is
that the unevenness is not a CSS problem and not a format problem: it is that
fourteen marks of wildly different proportion are being poured into one box.

## What the page gives each mark

Every supporter cell is `175 × 108`, padded `18px 20px`, so the artwork gets a
box of **135 × 72 CSS px**. The image is `max-width: 100%; max-height: 72px`,
so each mark is scaled down until it fits — whichever edge it hits first.

Two cells in the last row come out `190px` wide rather than `175px`, because
the row's remaining space is shared out by flexbox. Cărturești and Jazz Banat
are therefore about 11% larger than everything else. Worth pinning the cell
width before doing any of this by hand.

## The problem is height, not width

Twelve of the fourteen marks are wide enough to hit `max-width` first, so they
all come out exactly 135px across and the **widths look even**. What varies is
the height, from 41px to 72px — a 1.75× spread. The two squarish marks have the
opposite problem and are starved of width.

| file | ratio | renders today |
| --- | --- | --- |
| `inp.png` | 3.30 | 135 × **41** |
| `visit-timisoara.jpg` | 3.28 | 135 × **41** |
| `djc.svg` | 3.20 | 135 × 42 |
| `jazz-banat.png` | 3.00 | 150 × 50 |
| `faina.png` | 2.69 | 135 × 50 |
| `carturesti.svg` | 2.69 | 150 × 56 |
| `kek.svg` | 2.43 | 135 × 55 |
| `oradea-heritage.png` | 2.18 | 135 × 62 |
| `youth-heritage-europe.png` | 2.07 | 135 × 65 |
| `uvt-arte.svg` | 2.00 | 135 × 68 |
| `institutul-polonez.svg` | 2.00 | 135 × 68 |
| `oar-timis.svg` | 1.68 | **121** × 72 |
| `cicasp.svg` | 1.23 | **88** × 72 |
| `imagine-heritage.png` | 1.04 | **75** × 72 |

INP and Visit Timișoara read as roughly half the size of Imagine Heritage, even
though both are filling their box exactly as told.

## What to export

Do not resize the marks. Put every one on an **identical transparent canvas of
560 × 280 px** — for an SVG, `viewBox="0 0 560 280"` — with the artwork centred
and scaled to the size in the table below.

Every file then renders at exactly **135 × 67.5 CSS px**: the same footprint for
all of them, with no per-logo CSS anywhere. The sizes below give each mark the
same optical area inside that canvas, so a wide wordmark and a square emblem
carry the same weight.

| file | draw at, inside 560 × 280 |
| --- | --- |
| `inp.png` | 426 × 129 |
| `visit-timisoara.jpg` | 425 × 130 |
| `djc.svg` | 420 × 131 |
| `jazz-banat.png` | 406 × 135 |
| `faina.png` | 385 × 143 |
| `carturesti.svg` | 385 × 143 |
| `kek.svg` | 366 × 150 |
| `oradea-heritage.png` | 346 × 159 |
| `youth-heritage-europe.png` | 338 × 163 |
| `uvt-arte.svg` | 332 × 166 |
| `institutul-polonez.svg` | 332 × 166 |
| `oar-timis.svg` | 304 × 181 |
| `cicasp.svg` | 260 × 212 |
| `imagine-heritage.png` | 239 × 230 |

Equal bounding-box area is a good proxy, not the last word. A solid, dense mark
reads heavier than an airy wordmark covering the same area, so once these are
in, nudge the heaviest down by 5–8% by eye.

## SVG or transparent PNG?

**Keep SVG.** It stays crisp at 2× and 3×, the greyscale / multiply /
dark-mode-invert filters treat both formats identically, and an SVG takes the
canvas treatment just as well — set the `viewBox` and centre the artwork inside
it. Format is not what is causing the unevenness, so swapping formats will not
fix it.

Three files are worth changing anyway, for reasons other than sharpness:

- **`visit-timisoara.jpg` — replace it.** The only JPEG, no alpha, a white
  plate. It only looks right because the whole row is set in
  `mix-blend-mode: multiply` to knock the white out. Transparent PNG or SVG.
- **`cjt.svg` (132 KB, two embedded rasters) and `scart.svg` (one)** are PNGs in
  an SVG wrapper — the worst of both. Neither is on the page at the moment, so
  this only matters if they come back.
- `mnab.svg`, `oar-timis.svg` and `heritage-of-timisoara.svg` are genuine
  vectors but heavy at 68–76 KB. SVGO would cut them a long way. Purely a
  weight question; nothing visible.

## Not covered here

The three role marks at the top — Prin Banat, Heritage of Timișoara, Centrul de
Proiecte — are sized by their own CSS and are deliberately larger, with Prin
Banat larger again as the organiser. Normalising those is a separate decision.

Unused files, safe to delete whenever: `arhabito.svg`, `cjt.svg`, `mnab.svg`,
`scart.svg`, `temporar.png`.
