<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->isAdmin(); }

    public function rules(): array
    {
        return [
            'status'  => 'required|in:pending,processing,supplier_preparing,ready_at_supplier,picked_up,delivered,cancelled',
            'comment' => 'nullable|string|max:500',
        ];
    }
}
