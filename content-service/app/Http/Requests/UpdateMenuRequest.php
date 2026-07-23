<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'slug' => ['required', 'string', 'max:200', Rule::unique('menus', 'slug')->ignore($id)],
            'title' => 'required|string|max:200',
            'is_active' => 'required',
            'is_show_front' => 'required|boolean',
            'order' => 'required',
            'layout_id' => 'required_if:has_sub,false',
            'is_accessible' => 'boolean',
            'is_show_home' => 'boolean',
            'is_show_tag' => 'boolean',
        ];
    }
}
