<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\CategoryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // enforced by your existing permission middleware
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): CategoryData
    {
        return CategoryData::fromArray($this->validated());
    }
}
