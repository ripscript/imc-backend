<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncApproversRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'approvers' => ['required', 'array', 'min:1'],
            'approvers.*.approver_id' => ['required', 'integer', 'exists:users,id', 'distinct'],
            'approvers.*.sequence' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
