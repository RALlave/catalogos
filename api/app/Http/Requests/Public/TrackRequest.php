<?php

namespace App\Http\Requests\Public;

use App\Enums\StatType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sólo se acepta `share` desde el navegador.
     *
     * Las visitas y las vistas de producto las cuenta la API cuando sirve el
     * catálogo, así que dejarlas entrar por acá sería una forma de inflarlas.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([StatType::Share->value])],
            'product_slug' => ['nullable', 'string', 'max:255'],
        ];
    }
}
