<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductImagesRequest extends FormRequest
{
    public const MAX_IMAGES = 10;

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
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    /**
     * The limit counts the images already stored, not only the ones being uploaded.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $stored = $this->route('product')?->images()->count() ?? 0;
                $incoming = count($this->file('images') ?? []);

                if ($stored + $incoming > self::MAX_IMAGES) {
                    $validator->errors()->add(
                        'images',
                        'A product cannot have more than '.self::MAX_IMAGES.' images. It already has '.$stored.'.'
                    );
                }
            },
        ];
    }
}
