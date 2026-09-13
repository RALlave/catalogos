<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlatformStoreLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * SVG queda fuera a propósito: la subida se convierte a WebP con GD, que no
     * lee vectores.
     *
     * El nombre es opcional al subir —se toma el del archivo— y obligatorio al
     * renombrar, que es lo único que se edita.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->isMethod('PUT')) {
            return [
                'name' => ['required', 'string', 'max:100'],
            ];
        }

        return [
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
