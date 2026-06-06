<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogReplyRequest extends FormRequest
{
    public function authorize(): true
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reply' => 'required|string|max:2000',
        ];
    }
}
