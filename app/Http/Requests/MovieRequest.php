<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MovieRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // For updates, some fields might be optional if they're not being changed
        // But we'll keep them required for consistency
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'TypeOfFilm' => 'required|string',
            'duration' => 'required|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'TypeOfFilm.required' => 'The movie type field is required.',
            'duration.required' => 'The duration field is required.',
            'duration.integer' => 'The duration must be a number.',
            'duration.min' => 'The duration must be at least 1 minute.',
        ];
    }

    /**
     * Prepare the data for validation.
     * This helps ensure FormData values are properly accessible.
     */
    protected function prepareForValidation(): void
    {
        // Ensure all required fields are present
        // This is especially important for PUT requests with FormData
        $this->merge([
            'title' => $this->input('title', ''),
            'description' => $this->input('description', ''),
            'duration' => $this->input('duration', 0),
            'TypeOfFilm' => $this->input('TypeOfFilm', ''),
        ]);
    }
}
