<?php

namespace App\Http\Requests;

use App\Rules\UniqueSoftDelete;
use Illuminate\Foundation\Http\FormRequest;

class CreateKategoriFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_kategori_faq')],
        ];
    }
}
