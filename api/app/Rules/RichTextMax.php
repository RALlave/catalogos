<?php

namespace App\Rules;

use App\Support\RichText;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Length limit for rich text: counts the visible characters, not the HTML.
 */
class RichTextMax implements ValidationRule
{
    public function __construct(private readonly int $max) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && RichText::length($value) > $this->max) {
            $fail(__('The :attribute may not be greater than :max characters.', ['max' => $this->max]));
        }
    }
}
