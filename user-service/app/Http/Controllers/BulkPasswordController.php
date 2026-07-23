<?php

namespace App\Http\Controllers;

use App\Exports\BulkPasswordTemplateExport;
use App\Http\Requests\BulkPasswordImportRequest;
use App\Services\BulkPasswordService;
use Maatwebsite\Excel\Facades\Excel;

class BulkPasswordController extends Controller
{
    public function __construct(
        protected BulkPasswordService $bulkPasswordService
    ) {}

    public function template()
    {
        $fileName = 'bulk_update_password_template.xlsx';

        return Excel::download(new BulkPasswordTemplateExport, $fileName);
    }

    public function import(BulkPasswordImportRequest $request)
    {
        return $this->bulkPasswordService->import($request->file('file'));
    }
}
