<?php

namespace App\Http\Requests;

use App\Rules\SafeFileExtension;
use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', new SafeFileExtension],
        ];
    }
}
