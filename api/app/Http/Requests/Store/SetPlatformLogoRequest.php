<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class SetPlatformLogoRequest extends FormRequest
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
            'platform_logo_id' => ['required', 'integer', 'exists:platform_logos,id'],
        ];
    }
}
