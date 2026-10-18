<?php

namespace App\Support;

use DOMAttr;
use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMProcessingInstruction;
use DOMXPath;

/**
 * Allowlist sanitizer for rich-text bodies. Runs on every save regardless of
 * what the client claimed to send — the editor is UX, not a security boundary
 * (ADMIN_PANEL.md §7, SECURITY.md §6). Scripts, event handlers, `javascript:`
 * URLs and frames are dropped; anything not on the allowlist is unwrapped.
 */
class HtmlSanitizer
{
    /** @var list<string> */
    private const ALLOWED_TAGS = [
        'a', 'b', 'blockquote', 'br', 'code', 'em', 'figcaption', 'figure',
        'h2', 'h3', 'h4', 'hr', 'i', 'img', 'li', 'ol', 'p', 'pre', 's',
        'strong', 'table', 'tbody', 'td', 'th', 'thead', 'tr', 'u', 'ul',
    ];

    /** @var list<string> Removed entirely, keeping no inner content. */
    private const REMOVE_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input',
        'button', 'select', 'textarea', 'link', 'meta', 'svg', 'math',
        'noscript', 'template', 'applet', 'frame', 'frameset',
    ];

    /** @var array<string, list<string>> */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title'],
        'img' => ['src', 'alt', 'width', 'height'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
    ];

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        $document = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"?><html><body><div id="__sanitizer_root">'.$html.'</div></body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = (new DOMXPath($document))->query('//*[@id="__sanitizer_root"]')->item(0);

        if (! $root instanceof DOMNode) {
            return '';
        }

        self::sanitizeChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return trim($output);
    }

    private static function sanitizeChildren(DOMNode $node): void
    {
        $children = iterator_to_array($node->childNodes);

        foreach ($children as $child) {
            if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
                $node->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->nodeName);

            if (in_array($tag, self::REMOVE_WITH_CONTENT, true)) {
                $node->removeChild($child);

                continue;
            }

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                self::sanitizeChildren($child);

                while ($child->firstChild !== null) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            self::cleanAttributes($child, $tag);
            self::sanitizeChildren($child);
        }
    }

    private static function cleanAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];

        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (! $attribute instanceof DOMAttr) {
                continue;
            }

            $name = strtolower($attribute->nodeName);

            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && self::isDangerousUrl($attribute->nodeValue)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }
    }

    private static function isDangerousUrl(string $value): bool
    {
        $normalised = strtolower((string) preg_replace('/\s+/', '', $value));

        if (str_starts_with($normalised, 'javascript:') || str_starts_with($normalised, 'vbscript:')) {
            return true;
        }

        return str_starts_with($normalised, 'data:') && ! str_starts_with($normalised, 'data:image/');
    }
}
