<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerLoyaltyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['earn', 'redeem', 'adjustment'])],
            'points' => ['required', 'integer', 'min:1', 'max:100000000'],
            'direction' => ['required_if:type,adjustment', Rule::in(['add', 'subtract'])],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
