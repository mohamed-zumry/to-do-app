<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //Todo : add RBACK Auth , for POC give Access
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',  // Make description nullable
            'status' => 'required|in:pending,in-progress,completed',
            'files' => 'nullable|array',  // Validate files as an array
            'files.*' => 'file|mimes:jpeg,png,jpg,pdf|max:2048', // Validate each file
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Task name is required.',
            'status.required' => 'Task status is required.',
            'status.in' => 'The status must be one of: pending, in-progress, or completed.',
            'files.*.mimes' => 'Each file must be a valid file type (jpeg, png, jpg, pdf).',
            'files.*.max' => 'Each file must be less than 2MB.',
        ];
    }
}
