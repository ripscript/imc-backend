<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:menu,sub_menu,child_sub_menu',
            'slug' => 'required|array',
        ];
    }
}
