<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateChildSubMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sub_menu_id' => 'required|exists:sub_menus,id',
            'slug' => ['required', 'string', 'max:200', Rule::unique('child_sub_menus', 'slug')->where(function ($query) {
                $query->where('sub_menu_id', $this->sub_menu_id);
            })],
            'title' => 'required|string|max:200',
            'is_active' => 'required',
            'order' => 'required',
            'type' => 'required',
            'layout_id' => 'required',
            'is_accessible' => 'boolean',
            'is_show_home' => 'boolean',
            'is_show_tag' => 'boolean',
        ];
    }
}
