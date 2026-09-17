<?php

namespace App\Support;

use DOMDocument;
use DOMNode;

/**
 * A guest biography, made safe to render.
 *
 * The backoffice writes bios in a small editor that emits paragraphs and
 * bold/italic/underline; the Drive seeder writes plain text. Both pass through
 * here and come out as the same tiny, attribute-free HTML — so the profile
 * modal can render it with v-html without a way in for a script.
 *
 * The rule is an allowlist, not a blocklist: only these tags survive, and no
 * attribute on any of them. With no attributes there is no href, no style, no
 * on* handler — nothing that carries an attack — so the surface is the five
 * tags themselves. Everything else is unwrapped to its text (a stray <span>
 * keeps its words) except <script>/<style>, whose contents are dropped whole.
 *
 * Run on save and again on render: the store holds clean HTML, and rendering
 * never trusts the store to have stayed that way.
 */
class HtmlBio
{
    /** The only tags that live. `br` is void; the rest wrap their children. */
    private const ALLOWED = ['p', 'br', 'strong', 'em', 'u'];

    /** Editors and browsers reach for these; they mean the allowed ones. */
    private const MAP = ['b' => 'strong', 'i' => 'em', 'div' => 'p'];

    /** Content, not container: dropped whole rather than unwrapped to text. */
    private const DROP = ['script', 'style'];

    public static function clean(?string $raw): ?string
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return null;
        }

        // Plain text unless one of our own tags is actually in it — the
        // editor emits those, and a browser serialises a typed "<" as "&lt;",
        // so a bare angle bracket here means prose like "ages 5 < 10", not
        // markup. Routing that to the parser would eat it as a broken tag.
        if (! preg_match('#</?(?:p|br|strong|b|em|i|u|div)\b#i', $raw)) {
            return self::fromPlainText($raw);
        }

        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        // The XML encoding hint keeps UTF-8 intact; the flags stop DOMDocument
        // wrapping the fragment in <html><body> and a doctype.
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><body>'.$raw.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $dom->getElementsByTagName('body')->item(0);
        $html = $body ? self::render($body) : '';

        return self::tidy($html);
    }

    private static function render(DOMNode $node): string
    {
        $out = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $out .= htmlspecialchars($child->nodeValue, ENT_QUOTES, 'UTF-8');

                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::DROP, true)) {
                continue;
            }

            $tag = self::MAP[$tag] ?? $tag;
            $inner = self::render($child);

            if (! in_array($tag, self::ALLOWED, true)) {
                // Unknown tag: keep the words, drop the wrapper.
                $out .= $inner;

                continue;
            }

            $out .= $tag === 'br' ? '<br>' : '<'.$tag.'>'.$inner.'</'.$tag.'>';
        }

        return $out;
    }

    private static function fromPlainText(string $raw): ?string
    {
        $html = '';

        foreach (preg_split('/\n{2,}/', $raw) as $paragraph) {
            $paragraph = trim($paragraph);

            if ($paragraph === '') {
                continue;
            }

            $paragraph = htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8');
            $html .= '<p>'.str_replace("\n", '<br>', $paragraph).'</p>';
        }

        return $html === '' ? null : $html;
    }

    private static function tidy(string $html): ?string
    {
        // Empty paragraphs the editor leaves behind on a blank line.
        $html = preg_replace('#<p>(?:\s|<br>)*</p>#', '', $html);
        $html = trim($html);

        if ($html === '') {
            return null;
        }

        // Loose inline text with no block around it — wrap it, so the store is
        // always a run of paragraphs.
        if (! str_starts_with($html, '<p>')) {
            $html = '<p>'.$html.'</p>';
        }

        return $html;
    }
}
