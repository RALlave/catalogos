/*
 * Helpers for the HTML saved by RichTextEditor.
 */

function parse(html) {
    return new DOMParser().parseFromString(html, 'text/html').body.textContent ?? ''
}

/**
 * Text used by the field counter. It counts like the API limit: blocks add
 * nothing between them and a line break is one character.
 */
export function richTextCounter(html) {
    if (! html) {
        return ''
    }

    return parse(html.replace(/<br\s*\/?>/gi, ' ')).trim()
}

/**
 * Plain text to show in listings, with paragraphs and list items separated.
 */
export function richTextToPlain(html) {
    if (! html) {
        return ''
    }

    return parse(html.replace(/<br\s*\/?>|<\/(p|li|ul|ol)>/gi, ' '))
        .replace(/\s+/g, ' ')
        .trim()
}
