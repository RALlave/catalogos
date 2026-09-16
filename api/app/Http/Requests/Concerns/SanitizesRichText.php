<?php

namespace App\Http\Requests\Concerns;

use App\Support\RichText;

/**
 * Cleans the rich text fields before validating, so the length rule and the
 * stored value both see the HTML that is actually kept.
 */
trait SanitizesRichText
{
    /**
     * @param  array<int, string>  $fields
     */
    protected function sanitizeRichText(array $fields): void
    {
        foreach ($fields as $field) {
            if ($this->has($field) && (is_string($this->input($field)) || $this->input($field) === null)) {
                $this->merge([$field => RichText::sanitize($this->input($field))]);
            }
        }
    }
}
