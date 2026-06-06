<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'sometimes|in:planning,active,completed,archived',
            'project_manager_id' => 'sometimes|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The project name is required.',
            'name.max' => 'The project name must not exceed 255 characters.',
            'start_date.required' => 'Please provide a start date.',
            'end_date.required' => 'Please provide an end date.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            'status.in' => 'The status must be one of: planning, active, completed, archived.',
            'project_manager_id.exists' => 'The selected project manager does not exist.',
        ];
    }
}
