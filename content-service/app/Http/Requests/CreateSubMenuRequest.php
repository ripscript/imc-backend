<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSubMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_id' => 'required|exists:menus,id',
            'slug' => ['required', 'string', 'max:200', Rule::unique('sub_menus', 'slug')->where(function ($query) {
                $query->where('menu_id', $this->menu_id);
            })],
            'title' => 'required|string|max:200',
            'has_child' => 'required',
            'is_active' => 'required',
            'order' => 'required',
            'type' => 'required_if:has_child,false',
            'layout_id' => 'required_if:has_child,false',
            'is_accessible' => 'boolean',
            'is_show_home' => 'boolean',
            'is_show_tag' => 'boolean',
        ];
    }
}
