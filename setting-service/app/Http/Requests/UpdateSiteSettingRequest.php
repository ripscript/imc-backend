<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address' => 'required|string',
            'phone' => 'required|string',
            'wa_number' => 'required|string',
            'email' => 'required|string|email',
            'facebook_url' => 'nullable|string',
            'youtube_url' => 'nullable|string',
            'instagram_url' => 'nullable|string',
            'twitter_url' => 'nullable|string',
            'max_file_size' => 'nullable|integer',
        ];
    }
}
