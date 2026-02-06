<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarouselRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'link' => 'nullable|url|max:500',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default order if not provided
        if (!$this->has('order') || $this->order === null) {
            $this->merge([
                'order' => 0,
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
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'carousel title',
            'link' => 'carousel link',
            'description' => 'carousel description',
            'image' => 'carousel image',
            'order' => 'display order',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The carousel title is required.',
            'link.url' => 'The link must be a valid URL.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'The image size must not exceed 2MB.',
            'order.integer' => 'The order must be a number.',
            'order.min' => 'The order must be at least 0.',
        ];
    }
}
