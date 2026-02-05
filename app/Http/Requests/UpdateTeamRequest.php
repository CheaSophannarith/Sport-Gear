<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
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
            'league_id' => 'nullable|exists:leagues,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name')->ignore($this->team->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('teams', 'slug')->ignore($this->team->id),
            ],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
                'is_active' => false,
            ]);
        }

        // Convert empty string to null for league_id
        if ($this->league_id === '') {
            $this->merge(['league_id' => null]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'league_id' => 'league',
            'name' => 'team name',
            'slug' => 'URL slug',
            'logo' => 'team logo',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The team name is required.',
            'name.unique' => 'A team with this name already exists.',
            'slug.unique' => 'A team with this URL slug already exists.',
            'logo.image' => 'The file must be an image.',
            'logo.max' => 'The logo size must not exceed 2MB.',
            'league_id.exists' => 'The selected league does not exist.',
        ];
    }
}
