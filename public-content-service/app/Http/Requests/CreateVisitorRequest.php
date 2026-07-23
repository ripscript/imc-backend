<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateVisitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'user_agent' => 'nullable|string',
            'device_type' => 'nullable|string|max:100|in:desktop,tablet,mobile,unknown',
        ];
    }
}
