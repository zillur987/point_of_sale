<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\BrandData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('brands', 'name')->ignore($this->route('brand')?->id)->withoutTrashed(),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): BrandData
    {
        return BrandData::fromArray($this->validated());
    }
}
