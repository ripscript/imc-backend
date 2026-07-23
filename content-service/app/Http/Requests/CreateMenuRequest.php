<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:200', Rule::unique('menus', 'slug')],
            'title' => 'required|string|max:200',
            'has_sub' => 'required',
            'is_active' => 'required',
            'is_show_front' => 'required|boolean',
            'order' => 'required',
            'type' => 'required_if:has_sub,false',
            'layout_id' => 'required_if:has_sub,false',
            'is_accessible' => 'boolean',
            'is_show_home' => 'boolean',
            'is_show_tag' => 'boolean',
        ];
    }
}
