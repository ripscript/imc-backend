<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string',
            'type' => 'required|string',
            'is_active' => 'required|boolean',
            'order' => 'nullable|integer',
        ];
    }
}
