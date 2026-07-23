<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required',
            'type' => 'required|in:menu,sub_menu,child_sub_menu',
            'content_slug' => 'required',
        ];
    }
}
