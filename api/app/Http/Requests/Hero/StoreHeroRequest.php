<?php

namespace App\Http\Requests\Hero;

use App\Http\Requests\Concerns\SanitizesRichText;
use App\Rules\RichTextMax;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHeroRequest extends FormRequest
{
    use SanitizesRichText;

    /**
     * A carousel longer than this stops being a banner and nobody scrolls it.
     */
    public const MAX_HEROES = 10;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * The category only matters when the button goes to a category.
     */
    protected function prepareForValidation(): void
    {
        $this->sanitizeRichText(['text']);

        if ($this->has('link') && $this->input('link') !== 'category') {
            $this->merge(['category_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'media_id' => [
                'nullable',
                'integer',
                Rule::exists('media', 'id')->where('store_id', $this->user()->store?->id),
            ],
            'category_id' => [
                'nullable',
                'required_if:link,category',
                'integer',
                Rule::exists('categories', 'id')->where('store_id', $this->user()->store?->id),
            ],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:120'],
            'text' => ['nullable', 'string', new RichTextMax(255)],
            'button_text' => ['nullable', 'string', 'max:30'],
            'link' => ['sometimes', 'required', 'string', Rule::in(array_keys(config('catalog.hero_links')))],
            'align' => ['sometimes', 'required', 'string', Rule::in(config('catalog.hero_aligns'))],
            'order' => ['sometimes', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $heroes = $this->user()->store?->heroes()->count() ?? 0;

                if ($heroes >= self::MAX_HEROES) {
                    $validator->errors()->add('title', __('The store already has the maximum of :max heroes.', ['max' => self::MAX_HEROES]));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'media_id.exists' => 'The image does not belong to the store.',
            'category_id.exists' => 'The category does not belong to the store.',
            'category_id.required_if' => 'Choose the category the button goes to.',
        ];
    }
}
