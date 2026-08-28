<?php

namespace App\Http\Requests\Hero;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHeroRequest extends FormRequest
{
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
            'title' => ['sometimes', 'required', 'string', 'max:120'],
            'text' => ['nullable', 'string', 'max:255'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'active' => ['sometimes', 'boolean'],
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
