<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreCategoryRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',

            // Category filters
            'filters' => 'nullable|array',
            'filters.*.type' => 'required|string|in:brand,league,team,surface_type',
            'filters.*.required' => 'boolean',
            'filters.*.sort_order' => 'nullable|integer|min:0',

            // Variant sizes
            'variant_sizes' => 'nullable|array',
            'variant_sizes.*.value' => 'required|string|max:50',
            'variant_sizes.*.label' => 'required|string|max:100',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (empty($this->slug)) {
            $this->merge([
                'slug' => Str::lower(Str::snake($this->name)),
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

        if (!$this->has('sort_order')) {
            $this->merge([
                'sort_order' => 0,
            ]);
        }

        // Convert filters.*.required to boolean
        if ($this->has('filters') && is_array($this->input('filters'))) {
            $filters = $this->input('filters');
            foreach ($filters as $key => $filter) {
                if (isset($filter['required'])) {
                    $filters[$key]['required'] = in_array($filter['required'], ['1', 1, 'true', true, 'on', 'yes'], true);
                } else {
                    $filters[$key]['required'] = false;
                }
            }
            $this->merge(['filters' => $filters]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'category name',
            'slug' => 'URL slug',
            'description' => 'category description',
            'image' => 'category image',
            'sort_order' => 'sort order',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.unique' => 'A category with this name already exists.',
            'slug.unique' => 'A category with this URL slug already exists.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'The image size must not exceed 2MB.',
        ];
    }
}
