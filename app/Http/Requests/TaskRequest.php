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
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'status' => 'nullable|string|in:to_do,in_progress,in_review,completed,blocked',
            'deadline' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
        ];

        $method = $this->route()->getActionMethod();

        if (in_array($method, ['update', 'updateStatus'])) {
            foreach ($rules as $field => $rule) {
                if (str_starts_with($rule, 'required')) {
                    $rules[$field] = str_replace('required', 'sometimes', $rule);
                }
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The task name is required.',
            'name.max' => 'The task name must not exceed 255 characters.',
            'priority.in' => 'Priority must be one of: low, medium, high, critical.',
            'status.in' => 'Status must be one of: to_do, in_progress, in_review, completed, blocked.',
            'project_id.required' => 'Please select a project.',
            'project_id.exists' => 'The selected project does not exist.',
            'assigned_to.exists' => 'The selected user does not exist.',
        ];
    }
}
