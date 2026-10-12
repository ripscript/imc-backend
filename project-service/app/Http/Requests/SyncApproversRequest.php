<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncApproversRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approvers' => ['required', 'array'],
            'approvers.*.approver_id' => ['required', 'integer', 'exists:users,id', 'distinct'],
            'approvers.*.sequence' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
