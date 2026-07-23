<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content_id' => 'required|exists:contents,id',
            'event_name' => 'required|string',
            'is_range' => 'required|boolean',
            'is_active' => 'required|boolean',
            'datetime_start' => 'required',
            'datetime_end' => 'required_if:is_range,true',
        ];
    }
}
