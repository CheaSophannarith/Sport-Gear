<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->category->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($this->category->id),
            ],
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer|min:0',
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
                'slug' => Str::lower(Str::snake($this->name)),
            ]);
        }

        if (!$this->has('is_active')) {
            $this->merge([
                'is_active' => false,
            ]);
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
