<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;

class CustomerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $groupId = $this->route('customerGroup')?->id;

        return [
            'name' => ['required', 'string', 'max:120', 'unique:customer_groups,name' . ($groupId ? ',' . $groupId : '')],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
