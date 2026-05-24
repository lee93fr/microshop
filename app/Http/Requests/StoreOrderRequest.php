<?php
// app/Http/Requests/StoreOrderRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'delivery_mode'        => 'required|in:pickup,home',
            'delivery_address'     => 'nullable|required_if:delivery_mode,home|string|max:255',
            'delivery_city'        => 'nullable|required_if:delivery_mode,home|string|max:100',
            'delivery_postal_code' => 'nullable|required_if:delivery_mode,home|string|max:20',
            'delivery_country'     => 'nullable|string|max:100',
            'notes'                => 'nullable|string|max:1000',
            'payment_method'       => 'required|in:stripe,revolut,rib,cash',
        ];
    }
}
