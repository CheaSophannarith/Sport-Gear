<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductRequest extends FormRequest
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
            // Basic product info
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'features' => 'nullable|array',
            'base_price' => 'required|numeric|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',

            // Images
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',

            // Filter relationships (singular, not arrays)
            'brand_id' => 'nullable|exists:brands,id',
            'league_id' => 'nullable|exists:leagues,id',
            'team_id' => 'nullable|exists:teams,id',
            'surface_type_id' => 'nullable|exists:surface_types,id',

            // Product variants
            'variants' => 'required|array|min:1',
            'variants.*.size' => 'required|string|max:50',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.price_adjustment' => 'nullable|numeric',
            'variants.*.stock_quantity' => 'required|integer|min:0',
            'variants.*.low_stock_threshold' => 'nullable|integer|min:0',
            'variants.*.is_active' => 'boolean',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug if empty
        if (empty($this->slug)) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }

        // Convert is_featured to boolean
        if ($this->has('is_featured')) {
            $value = $this->input('is_featured');
            $this->merge([
                'is_featured' => in_array($value, ['1', 1, 'true', true, 'on', 'yes'], true),
            ]);
        } else {
            $this->merge(['is_featured' => false]);
        }

        // Convert is_active to boolean
        if ($this->has('is_active')) {
            $value = $this->input('is_active');
            $this->merge([
                'is_active' => in_array($value, ['1', 1, 'true', true, 'on', 'yes'], true),
            ]);
        } else {
            $this->merge(['is_active' => true]);
        }

        // Convert variant is_active to boolean
        if ($this->has('variants') && is_array($this->input('variants'))) {
            $variants = $this->input('variants');
            foreach ($variants as $key => $variant) {
                if (isset($variant['is_active'])) {
                    $variants[$key]['is_active'] = in_array($variant['is_active'], ['1', 1, 'true', true, 'on', 'yes'], true);
                } else {
                    $variants[$key]['is_active'] = true;
                }
            }
            $this->merge(['variants' => $variants]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'name' => 'product name',
            'slug' => 'URL slug',
            'description' => 'product description',
            'base_price' => 'base price',
            'featured_image' => 'featured image',
            'is_featured' => 'featured status',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'slug.unique' => 'A product with this URL slug already exists.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
            'base_price.required' => 'The base price is required.',
            'base_price.min' => 'The base price must be at least 0.',
            'featured_image.image' => 'The featured image must be an image file.',
            'featured_image.max' => 'The featured image size must not exceed 2MB.',
            'variants.required' => 'At least one product variant is required.',
            'variants.min' => 'At least one product variant is required.',
        ];
    }
}
