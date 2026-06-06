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
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Please provide a description of the work done.',
            'hours_worked.required' => 'Please enter the hours worked.',
            'hours_worked.min' => 'Minimum hours worked is 0.25.',
            'hours_worked.max' => 'Maximum hours worked is 24.',
            'attachment.max' => 'The attachment must not exceed 10MB.',
            'attachment.mimes' => 'The attachment must be a file of type: jpg, jpeg, png, pdf, doc, docx.',
        ];
    }
}
