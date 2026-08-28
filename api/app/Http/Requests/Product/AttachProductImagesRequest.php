<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AttachProductImagesRequest extends FormRequest
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
            'media_ids' => ['required', 'array', 'min:1'],
            'media_ids.*' => ['required', 'integer', 'distinct', 'exists:media,id'],
        ];
    }

    /**
     * El tope es el mismo que subiendo archivos, y las que el producto ya tiene
     * no vuelven a contar.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $product = $this->route('product');

                if (! $product) {
                    return;
                }

                $taken = $product->images()->pluck('media_id')->all();
                $incoming = array_diff($this->input('media_ids', []), $taken);
                $total = count($taken) + count($incoming);

                if ($total > StoreProductImagesRequest::MAX_IMAGES) {
                    $validator->errors()->add(
                        'media_ids',
                        'Un producto no puede tener más de '.StoreProductImagesRequest::MAX_IMAGES.' imágenes. Ya tiene '.count($taken).'.'
                    );
                }
            },
        ];
    }
}
