<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ReplaceProductImageRequest extends FormRequest
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
            'media_id' => ['required', 'integer', 'exists:media,id'],
        ];
    }

    /**
     * The same image is not shown twice in one product: swapping a photo for
     * another one the gallery already has is rejected.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $product = $this->route('product');
                $image = $this->route('image');

                if (! $product || ! $image) {
                    return;
                }

                $taken = $product->images()
                    ->whereKeyNot($image->id)
                    ->where('media_id', (int) $this->input('media_id'))
                    ->exists();

                if ($taken) {
                    $validator->errors()->add('media_id', 'Esa imagen ya está en el producto.');
                }
            },
        ];
    }
}
