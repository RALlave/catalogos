<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class CropMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The rectangle to keep, in pixels of the biggest variant (the file `url`
     * points at). The service keeps it inside the picture.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'x' => ['required', 'integer', 'min:0'],
            'y' => ['required', 'integer', 'min:0'],
            'width' => ['required', 'integer', 'min:1'],
            'height' => ['required', 'integer', 'min:1'],
        ];
    }
}
