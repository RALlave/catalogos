<?php

namespace App\Http\Requests\Store;

use App\Services\ThemeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::notIn(config('catalog.reserved_slugs')),
                Rule::unique('stores', 'slug')->ignore($this->user()->store?->id),
            ],
            'palette' => ['sometimes', 'string', Rule::in(app(ThemeService::class)->paletteKeys())],
            'radius' => ['sometimes', 'string', Rule::in(app(ThemeService::class)->optionKeys('radius'))],
            'nav' => ['sometimes', 'string', Rule::in(app(ThemeService::class)->optionKeys('nav'))],
            'banner' => ['sometimes', 'string', Rule::in(app(ThemeService::class)->optionKeys('banner'))],
            'hero_effect' => ['sometimes', 'string', Rule::in(config('catalog.hero_effects'))],
            'cart_enabled' => ['sometimes', 'boolean'],
            'waitlist_enabled' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'industry' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'tiktok' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'map_url' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:4', 'regex:/^[A-Za-z]{2,3}\.?$/'],
            'schedules' => ['nullable', 'array', 'max:14'],
            'schedules.*.days' => ['required', 'string', 'max:100'],
            'schedules.*.hours' => ['required', 'string', 'max:100'],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and single hyphens.',
            'currency.regex' => 'The currency must be 2 or 3 letters, with an optional dot, like Gs., PYG or usd.',
        ];
    }
}
