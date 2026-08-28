<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreWaitlistEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Los máximos coinciden con el tamaño de las columnas de
     * `waitlist_entries` y con el maxlength de los campos del catálogo.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_slug' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
        ];
    }
}
