<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
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
            'priority' => 'required|in:low,medium,high,critical',
            'deadline' => 'required|date',
            'estimated_hours' => 'required|numeric|min:0|max:9999',
            'project_id' => 'required_if:create|exists:projects,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The task name is required.',
            'name.max' => 'The task name must not exceed 255 characters.',
            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'Priority must be one of: low, medium, high, critical.',
            'deadline.required' => 'Please provide a deadline.',
            'estimated_hours.required' => 'Please provide estimated hours.',
            'estimated_hours.numeric' => 'Estimated hours must be a number.',
            'estimated_hours.min' => 'Estimated hours must be at least 0.',
            'estimated_hours.max' => 'Estimated hours must not exceed 9999.',
            'project_id.required_if' => 'Please select a project.',
            'project_id.exists' => 'The selected project does not exist.',
        ];
    }
}
