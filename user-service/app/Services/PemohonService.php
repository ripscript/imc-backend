<?php

namespace App\Services;

use App\Models\MsClassificationPemohon;
use App\Models\Pemohon;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\UpdatePemohonByAdminDTO;
use App\Services\DTOs\UpdatePemohonByPemohonDTO;
use Illuminate\Support\Facades\DB;

class PemohonService
{
    public function __construct(
        protected QueryService $queryService,
        protected EmailTemplateService $emailService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('pemohon')
            ->select(
                'id',
                'registration_number',
                'name',
                'status',
                'email',
                'created_at'
            )
            ->whereNull('deleted_at');

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['registration_number', 'name', 'email']);
    }

    public function detail($id)
    {
        $user = Pemohon::findOrFail($id);
        $user->classification_pemohon = $user->classification?->name;

        return $user;
    }

    public function updateByPemohon(UpdatePemohonByPemohonDTO $dto)
    {
        DB::beginTransaction();
        try {
            $id = auth('pemohon')->id();
            $pemohon = Pemohon::findOrFail($id);
            $peroranganId = MsClassificationPemohon::getPeroranganId();

            $pemohon->username = $dto->username;
            $pemohon->name = $dto->name;
            $pemohon->identity_number = $dto->identity_number;
            $pemohon->gender = $dto->gender;
            $pemohon->phone_number = $dto->phone_number;
            $pemohon->address = $dto->address;
            $pemohon->organization = $dto->organization;
            $pemohon->job_title = $dto->job_title;
            $pemohon->classification_pemohon_id = $dto->classification_pemohon_id;
            $pemohon->identity_card_file_path = $dto->identity_card_file_path;
            if ($pemohon->classification_pemohon_id != 1) {
                $pemohon->legal_document_file_path = $dto->legal_document_file_path;
            }

            $requiredFields = [
                'name', 'identity_number', 'gender', 'phone_number',
                'address', 'organization', 'job_title',
                'classification_pemohon_id', 'identity_card_file_path',
            ];
            if ($pemohon->classification_pemohon_id != $peroranganId) {
                $requiredFields[] = 'legal_document_file_path';
            }

            $isComplete = true;
            foreach ($requiredFields as $field) {
                if (empty($pemohon->{$field})) {
                    $isComplete = false;
                    break;
                }
            }

            if ($pemohon->status != Pemohon::STATUS['DIBLOKIR']) {
                if ($isComplete) {
                    $pemohon->status = Pemohon::STATUS['DATA_TERVERIFIKASI'];
                } elseif ($pemohon->is_email_verified) {
                    $pemohon->status = Pemohon::STATUS['EMAIL_TERVERIFIKASI'];
                } else {
                    $pemohon->status = Pemohon::STATUS['BELUM_TERVERIFIKASI'];
                }
            }

            $pemohon->updated_by = $pemohon->name;
            $pemohon->save();

            DB::commit();

            return $pemohon;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateByAdmin(int $id, UpdatePemohonByAdminDTO $dto)
    {
        DB::beginTransaction();
        try {
            $data = Pemohon::findOrFail($id);

            $status = $dto->status;
            if ($status == 1) {
                $data->email_verified_at = now();
                $data->otp = null;
                $data->otp_created_at = null;
            }
            $data->status = $status;
            $data->save();

            DB::commit();

            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function resendVerification(int $id)
    {
        DB::beginTransaction();
        try {
            $user = Pemohon::findOrFail($id);
            $user->otp = Pemohon::generateOtp();
            $user->otp_created_at = now();
            $user->email_verified_at = null;
            $user->status = Pemohon::STATUS['BELUM_TERVERIFIKASI'];
            $user->save();

            DB::commit();

            $this->emailService->sendTemplate('Pendaftaran Pemohon', $user->email, [
                'otp' => $user->otp,
                'nama' => $user->name,
            ], 'PPID Kemlu – Kode OTP Verifikasi Akun');

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete(int $id)
    {
        DB::beginTransaction();
        try {
            $data = Pemohon::findOrFail($id);
            $data->delete();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
