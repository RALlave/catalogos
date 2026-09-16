<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Text written with the panel rich text editor.
 *
 * It is stored as HTML, but only with the tags the editor can produce:
 * anything else is removed when saving. Limits count the visible text, not
 * the HTML, so a bold word does not eat characters the owner cannot see.
 */
class RichText
{
    /** The only tags that survive sanitizing. */
    public const TAGS = ['p', 'br', 'strong', 'em', 'u', 'ul', 'ol', 'li'];

    /**
     * Tags removed but keeping their text. Anything else not allowed is
     * dropped whole, content included (scripts, styles).
     */
    private const UNWRAP = ['a', 'b', 'i', 'span', 'div', 'section', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    /**
     * Clean HTML ready to store, or null when there is no visible text
     * (an empty editor still sends `<p></p>`).
     */
    public static function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $config = (new HtmlSanitizerConfig())->withMaxInputLength(-1);

        foreach (self::TAGS as $tag) {
            $config = $config->allowElement($tag);
        }

        foreach (self::UNWRAP as $tag) {
            $config = $config->blockElement($tag);
        }

        $clean = trim((new HtmlSanitizer($config))->sanitize($html));

        return self::length($clean) > 0 ? $clean : null;
    }

    /**
     * Characters the owner sees. It matches the editor counter: blocks add
     * nothing between them and a line break counts as one character.
     */
    public static function length(?string $html): int
    {
        if ($html === null || $html === '') {
            return 0;
        }

        $text = preg_replace('/<br\s*\/?>/i', ' ', $html);

        return mb_strlen(trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    /**
     * Plain text for places that cannot show formatting: card excerpts, meta
     * description, Open Graph. Paragraphs and list items become spaces.
     */
    public static function toText(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return null;
        }

        $text = preg_replace('/<br\s*\/?>|<\/(p|li|ul|ol)>/i', ' ', $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        return $text === '' ? null : $text;
    }
}
