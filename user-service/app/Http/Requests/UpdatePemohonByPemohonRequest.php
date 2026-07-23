<?php

namespace App\Http\Requests;

use App\Rules\UniqueSoftDelete;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePemohonByPemohonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = auth('pemohon')->id();
        $peroranganId = \App\Models\MsClassificationPemohon::getPeroranganId();

        return [
            'username' => ['required', 'string', 'max:100', new UniqueSoftDelete('pemohon', 'username', $id)],
            'name' => 'required|string|max:255',
            'identity_number' => ['required', 'max:16', new UniqueSoftDelete('pemohon', 'identity_number', $id)],
            'gender' => 'required|in:1,2',
            'phone_number' => 'required|string|max:50',
            'address' => 'required|string',
            'organization' => 'required|string|max:100',
            'job_title' => 'required|string|max:100',
            'classification_pemohon_id' => 'required|exists:ms_classification_pemohon,id',
            'identity_card_file_path' => 'required|string',
            'legal_document_file_path' => "nullable|required_unless:classification_pemohon_id,{$peroranganId}|string",
        ];
    }
}
