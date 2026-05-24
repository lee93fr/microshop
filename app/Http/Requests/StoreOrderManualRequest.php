<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderManualRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->isAdmin(); }

    public function rules(): array
    {
        return [
            'user_id'                  => 'required|exists:users,id',
            'delivery_mode'            => 'required|in:pickup,home',
            'delivery_fee'             => 'nullable|numeric|min:0',
            'delivery_address'         => 'nullable|required_if:delivery_mode,home|string|max:255',
            'delivery_city'            => 'nullable|required_if:delivery_mode,home|string|max:100',
            'delivery_postal_code'     => 'nullable|required_if:delivery_mode,home|string|max:20',
            'delivery_country'         => 'nullable|string|max:100',
            'notes'                    => 'nullable|string',
            'supplier_notes'           => 'nullable|string',
            'payment_method'           => 'required|in:stripe,revolut,rib,cash',
            'payment_status'           => 'required|in:unpaid,partial,paid',
            'payment_link'             => 'nullable|url',
            'discount'                 => 'nullable|numeric|min:0',
            'status'                   => 'required|in:pending,processing,supplier_preparing,ready_at_supplier,picked_up,delivered,cancelled',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.unit_price'       => 'required|numeric|min:0',
            'items.*.purchase_price'   => 'required|numeric|min:0',
        ];
    }
}
