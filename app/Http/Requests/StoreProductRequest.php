<?php
// app/Http/Requests/StoreProductRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->isAdmin(); }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'category_id'    => 'required|exists:categories,id',
            'purchase_price'  => 'required|numeric|min:0',
            'sale_price'      => 'required|numeric|min:0',
            'suggested_price' => 'nullable|numeric|min:0',
            'unit'           => 'required|string|max:50',
            'volume_ml'      => 'nullable|integer|min:0',
            'alcohol_degree' => 'nullable|numeric|min:0|max:100',
            'sku'            => "nullable|string|max:100|unique:products,sku,{$productId}",
            'image'          => 'nullable|image|max:5120',
            'image_remote_url' => 'nullable|url|max:2048',
            'is_active'      => 'boolean',
            'is_new'         => 'boolean',
        ];
    }
}
