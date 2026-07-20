<?php

namespace App\Http\Controllers;

use App\Exports\BulkPasswordTemplateExport;
use App\Helpers\ApiResponse;
use App\Helpers\LogActivity;
use App\Imports\BulkPasswordImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BulkPasswordController extends Controller
{
    public function template()
    {
        $fileName = 'bulk_update_password_template.xlsx';
        return Excel::download(new BulkPasswordTemplateExport, $fileName);
    }

    public function import(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls'
            ]);

            $import = new BulkPasswordImport();
            Excel::import($import, $request->file('file'));

            $invalidEmails = $import->getInvalidEmails();
            $totalRows = $import->getTotalRows();

            $invalidEmails = $import->getInvalidEmails();
            $totalRows = $import->getTotalRows();

            if ($totalRows === 0) {
                return ApiResponse::error('Sheet "Template Update" kosong atau tidak ditemukan.', '', 422);
            }

            if (count($invalidEmails) === $totalRows) {
                return ApiResponse::error('Semua email tidak ditemukan di sistem.', '', 422);
            }
            
            DB::commit();
            
            LogActivity::createCustom('pengguna', 'Melakukan pembaruan password secara massal melalui unggahan excel', null, auth('admin')->user());

            if (!empty($invalidEmails)) {
                $message = 'Beberapa email tidak ditemukan di sistem: ';
                $message .= implode(', ', $invalidEmails);
                return ApiResponse::error('Proses update password berhasil sebagian', $message);
            }

            return ApiResponse::success('', 'Pembaruan password massal berhasil dilakukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
