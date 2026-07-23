<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Helpers\LogActivity;
use App\Imports\BulkPasswordImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BulkPasswordService
{
    public function import(UploadedFile $file)
    {
        DB::beginTransaction();
        try {
            $import = new BulkPasswordImport;
            Excel::import($import, $file);

            $invalidEmails = $import->getInvalidEmails();
            $totalRows = $import->getTotalRows();

            if ($totalRows === 0) {
                DB::rollBack();

                throw new ValidationException('Sheet "Template Update" kosong atau tidak ditemukan.');
            }

            if (count($invalidEmails) === $totalRows) {
                DB::rollBack();

                throw new ValidationException('Semua email tidak ditemukan di sistem.');
            }

            DB::commit();

            LogActivity::createCustom('pengguna', 'Melakukan pembaruan password secara massal melalui unggahan excel', null, auth('admin')->user());

            if (! empty($invalidEmails)) {
                $message = 'Beberapa email tidak ditemukan di sistem: ';
                $message .= implode(', ', $invalidEmails);

                return [
                    'success' => false,
                    'message' => 'Proses update password berhasil sebagian',
                    'errors' => $message,
                ];
            }

            return [
                'success' => true,
                'message' => 'Pembaruan password massal berhasil dilakukan.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
