<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkLogRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string',
            'hours_worked' => 'required|numeric|min:0.25|max:24',
            'attachment' => 'nullable|string',
            'logged_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Please provide a description of the work done.',
            'hours_worked.required' => 'Please enter the hours worked.',
            'hours_worked.min' => 'Minimum hours worked is 0.25.',
            'hours_worked.max' => 'Maximum hours worked is 24.',
        ];
    }
}
