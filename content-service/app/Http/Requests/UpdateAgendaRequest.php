<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_name' => 'required|string',
            'is_range' => 'required|boolean',
            'is_active' => 'required|boolean',
            'datetime_start' => 'required',
            'datetime_end' => 'required_if:is_range,true',
        ];
    }
}
