<?php

namespace App\Http\Requests\Hero;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHeroRequest extends FormRequest
{
    /**
     * A carousel longer than this stops being a banner and nobody scrolls it.
     */
    private const MAX_HEROES = 10;

    public function authorize(): bool
    {
        return true;
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
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:120'],
            'text' => ['nullable', 'string', 'max:255'],
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
        ];
    }
}
