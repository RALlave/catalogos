<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
        $storeId = $this->user()->store?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->where('store_id', $storeId),
            ],
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'specs' => ['nullable', 'array', 'max:20'],
            'specs.*.label' => ['required', 'string', 'max:100'],
            'specs.*.type' => ['nullable', 'string', 'in:colors'],
            'specs.*.value' => ['nullable', 'string', 'max:255'],
            'specs.*.values' => ['nullable', 'array', 'max:20'],
            'specs.*.values.*' => ['string', 'max:50'],
            'benefits' => ['nullable', 'array', 'max:10'],
            'benefits.*' => ['string', 'max:150'],
            'badges' => ['nullable', 'array', 'max:4'],
            'badges.*.type' => ['nullable', 'string', 'in:discount,strong'],
            'badges.*.text' => ['required', 'string', 'max:40'],
            'badges.*.detail' => ['nullable', 'string', 'max:40'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'required_with:sale_price'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99', 'lt:price'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('store_id', $storeId),
            ],
            'featured' => ['sometimes', 'boolean'],
            'visible' => ['sometimes', 'boolean'],
            'sold_out' => ['sometimes', 'boolean'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers and single hyphens.',
            'slug.unique' => 'The store already has a product with that slug.',
            'price.required_with' => 'A sale price needs a regular price to compare against.',
            'sale_price.lt' => 'The sale price must be lower than the regular price.',
            'category_id.exists' => 'The selected category does not belong to the store.',
        ];
    }
}
