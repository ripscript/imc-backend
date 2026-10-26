<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveTimelineRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_date' => ['required', 'date'],
            'target_end_date' => ['required', 'date'],
            'stages' => ['nullable', 'array'],
            'stages.*.id' => ['required', 'integer'],
            'stages.*.start_date' => ['nullable', 'date'],
            'stages.*.end_date' => ['nullable', 'date'],
            'stages.*.depends_on_project_stage_id' => ['nullable', 'integer'],
        ];
    }
}
