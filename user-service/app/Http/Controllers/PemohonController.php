<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\LogActivity;
use App\Models\MsClassificationPemohon;
use App\Models\Pemohon;
use App\Rules\UniqueSoftDelete;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PemohonController extends Controller
{
    protected $emailService;

    public function __construct()
    {
        parent::__construct();
        $this->emailService = new EmailTemplateService();
    }

    public function index(Request $request)
    {
        try {
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
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['registration_number', 'name', 'email']);

            return ApiResponse::success($data, 'Berhasil mengambil data pemohon');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $user = Pemohon::findOrFail($id);
            $user->classification_pemohon = $user->classification?->name;
            return ApiResponse::success($user, 'Berhasil mengambil data detail pemohon');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function updateByPemohon(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = auth('pemohon')->id();
            $data = Pemohon::findOrFail($id);
            $peroranganId = MsClassificationPemohon::getPeroranganId();

            $request->validate([
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
            ]);

            $data->username = $request->username;
            $data->name = $request->name;
            $data->identity_number = $request->identity_number;
            $data->gender = $request->gender;
            $data->phone_number = $request->phone_number;
            $data->address = $request->address;
            $data->organization = $request->organization;
            $data->job_title = $request->job_title;
            $data->classification_pemohon_id = $request->classification_pemohon_id;
            $data->identity_card_file_path = $request->identity_card_file_path;
            if ($data->classification_pemohon_id != 1) {
                $data->legal_document_file_path = $request->legal_document_file_path;
            }

            $requiredFields = [
                'name',
                'identity_number',
                'gender',
                'phone_number',
                'address',
                'organization',
                'job_title',
                'classification_pemohon_id',
                'identity_card_file_path'
            ];
            if ($data->classification_pemohon_id != $peroranganId) {
                $requiredFields[] = 'legal_document_file_path';
            }

            $isComplete = true;
            foreach ($requiredFields as $field) {
                if (empty($data->{$field})) {
                    $isComplete = false;
                    break;
                }
            }

            if ($data->status != Pemohon::STATUS['DIBLOKIR']) {
                if ($isComplete) {
                    $data->status = Pemohon::STATUS['DATA_TERVERIFIKASI'];
                } elseif ($data->is_email_verified) {
                    $data->status = Pemohon::STATUS['EMAIL_TERVERIFIKASI'];
                } else {
                    $data->status = Pemohon::STATUS['BELUM_TERVERIFIKASI'];
                }
            }
            
            $data->updated_by = $data->name;
            $data->save();
            
            DB::commit();

            LogActivity::createCustom('pemohon', 'Memperbarui profil', $data, auth('pemohon')->user());

            return ApiResponse::success($data, 'Profil berhasil diperbarui');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateByAdmin(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Pemohon::findOrFail($id);
            
            $request->validate([
                'status' => 'required|integer|in:1,2,3'
            ]);

            if ($request->status == 1) {
                $data->email_verified_at = now();
                $data->otp = null;
                $data->otp_created_at = null;
            }
            $data->status = $request->status;
            $data->save();

            LogActivity::create('pemohon', $data, 'update', auth('admin')->user());

            DB::commit();
            return ApiResponse::success($data, 'Berhasil memperbarui status pemohon');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function resendVerification($id)
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
                'nama' => $user->name
            ], 'PPID Kemlu – Kode OTP Verifikasi Akun');

            return ApiResponse::success('', 'Berhasil mengirimkan email verifikasi ke pemohon');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function delete(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Pemohon::findOrFail($id);
            $oldPemohon = $data;
            $data->delete();

            LogActivity::create('pemohon', $oldPemohon, 'delete', auth('admin')->user());

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus pemohon');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
