<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\DTOs\UnitData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unitId = $this->route('unit')?->id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('units', 'name')->ignore($unitId)->withoutTrashed()],
            'short_name' => ['required', 'string', 'max:20', Rule::unique('units', 'short_name')->ignore($unitId)->withoutTrashed()],
            'allow_decimal' => ['sometimes', 'boolean'],
        ];
    }

    public function toDto(): UnitData
    {
        return UnitData::fromArray($this->validated());
    }
}
