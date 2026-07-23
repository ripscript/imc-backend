<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LockImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:20480',
        ];
    }
}
