<?php

namespace App\Http\Requests;

use App\Rules\UniqueSoftDelete;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'title' => ['required', 'string', 'max:255', new UniqueSoftDelete('ms_faq', 'title', $id)],
            'description' => 'required|string',
            'is_active' => 'required|boolean',
            'kategori_faq_id' => 'required',
        ];
    }
}
