<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlatformLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * SVG queda fuera a propósito: la subida se convierte a WebP con GD, que
     * no lee vectores.
     *
     * El ícono no pide medidas: se acepta cualquier imagen, aunque el navegador
     * sólo ofrezca instalar la aplicación si es cuadrada y de 512 px o más.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ];
    }
}
