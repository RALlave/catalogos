<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class StoreImageRequest extends FormRequest
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
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.$this->maxKilobytes(),
            ],
        ];
    }

    /**
     * The cover is displayed full width, so it is allowed to be heavier than the logo.
     */
    private function maxKilobytes(): int
    {
        return $this->routeIs('*.cover.*') ? 4096 : 2048;
    }
}
