<?php

namespace App\Exports;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SurveyKepuasanExport implements WithMultipleSheets
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function sheets(): array
    {   
        return [
            new ResponseSurveyKepuasanSheet($this->request),
            new RekapSurveyKepuasanSheet($this->request)
        ];
    }
}
