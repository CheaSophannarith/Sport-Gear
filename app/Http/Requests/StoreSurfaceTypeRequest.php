<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreSurfaceTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:surface_types,name',
            'slug' => 'nullable|string|max:255|unique:surface_types,slug',
            'code' => 'nullable|string|max:10|unique:surface_types,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (empty($this->slug)) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }

        // Convert any value to boolean
        if ($this->has('is_active')) {
            $value = $this->input('is_active');
            // Convert '1', 'true', 1, true to boolean true
            // Convert '0', 'false', 0, false, null, '' to boolean false
            $this->merge([
                'is_active' => in_array($value, ['1', 1, 'true', true, 'on', 'yes'], true),
            ]);
        } else {
            $this->merge([
                'is_active' => true,
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'surface type name',
            'slug' => 'URL slug',
            'code' => 'surface code',
            'description' => 'description',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The surface type name is required.',
            'name.unique' => 'A surface type with this name already exists.',
            'slug.unique' => 'A surface type with this URL slug already exists.',
            'code.unique' => 'A surface type with this code already exists.',
            'code.max' => 'The surface code must not exceed 10 characters.',
        ];
    }
}
