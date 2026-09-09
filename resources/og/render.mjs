/*
 * Renders the sharing cards in this folder to public/assets/images/og/.
 *
 * Every *.html here becomes a PNG of the same name. The card is a real page
 * loading the site's real stylesheet, so it is screenshotted rather than drawn:
 * what a feed shows is what the browser shows.
 *
 * Usage: node resources/og/render.mjs [name ...]     (default: all of them)
 *
 * Needs playwright and a Chromium. Both are usually already about — if not,
 * `npm i -D playwright && npx playwright install chromium`.
 */
import { execSync } from 'node:child_process';
import { mkdir, readdir } from 'node:fs/promises';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

/*
 * From the project's own node_modules if it is there, otherwise from wherever
 * npm keeps its global installs: this is a tool for making an image, not a
 * dependency of the site, and it has no business in package.json.
 */
async function playwright() {
    try {
        return await import('playwright');
    } catch {
        const root = execSync('npm root -g', { encoding: 'utf8' }).trim();

        return import(pathToFileURL(join(root, 'playwright', 'index.mjs')).href);
    }
}

const { chromium } = await playwright();

const here = dirname(fileURLToPath(import.meta.url));
const out = resolve(here, '../../public/assets/images/og');

// The size Facebook, LinkedIn, Slack and X all crop from. Anything else is
// letterboxed or cropped by somebody's idea of the middle.
const WIDTH = 1200;
const HEIGHT = 630;

const only = process.argv.slice(2).map((n) => n.replace(/\.html$/, ''));
const cards = (await readdir(here))
    .filter((f) => f.endsWith('.html'))
    .map((f) => f.replace(/\.html$/, ''))
    .filter((n) => only.length === 0 || only.includes(n));

if (cards.length === 0) {
    console.error(only.length ? `No card named ${only.join(', ')}` : 'No cards to render.');
    process.exit(1);
}

await mkdir(out, { recursive: true });

const browser = await chromium.launch();
const page = await browser.newPage({
    viewport: { width: WIDTH, height: HEIGHT },
    // The card is drawn once at 1x. A 2x render is sharper on a retina feed but
    // doubles the bytes, and every network downsamples it anyway.
    deviceScaleFactor: 1,
});

for (const name of cards) {
    await page.goto(`file://${join(here, `${name}.html`)}`, { waitUntil: 'networkidle' });

    // Manrope arrives from Google Fonts; screenshotting before it lands gives a
    // card set in whatever the system fallback is.
    await page.evaluate(() => document.fonts.ready);

    // The flying lines start invisible — the stroke is dashed off and drawn in.
    // Let the animation get somewhere before taking the picture.
    await page.waitForTimeout(1200);

    const card = page.locator('.og');
    const box = await card.boundingBox();

    if (Math.round(box.width) !== WIDTH || Math.round(box.height) !== HEIGHT) {
        throw new Error(
            `${name}: the card is ${Math.round(box.width)} × ${Math.round(box.height)}, not ${WIDTH} × ${HEIGHT}. `
            + 'Something inside it is pushing it out of shape.'
        );
    }

    const file = join(out, `${name}.png`);
    await card.screenshot({ path: file });
    console.log(`${name}.html  →  ${file}`);
}

await browser.close();
