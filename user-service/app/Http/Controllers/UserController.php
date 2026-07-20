<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\LogActivity;
use App\Models\MsPerwakilan;
use App\Models\MsRole;
use App\Models\MsSatuanKerja;
use App\Models\MsSubSatuanKerja;
use App\Models\User;
use App\Rules\UniqueSoftDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('users')
                ->join('ms_roles', 'users.role_id', '=', 'ms_roles.id')
                ->leftJoin('ms_satuan_kerja', 'users.satuan_kerja_id', '=', 'ms_satuan_kerja.id')
                ->leftJoin('ms_sub_satuan_kerja', 'users.sub_satuan_kerja_id', '=', 'ms_sub_satuan_kerja.id')
                ->leftJoin('ms_perwakilan', 'users.perwakilan_id', '=', 'ms_perwakilan.id')
                ->select(
                    'users.id', 
                    'users.name', 
                    'username', 
                    'email', 
                    'status',
                    'ms_roles.name as role', 
                    'ms_satuan_kerja.name as satuan_kerja',
                    'ms_sub_satuan_kerja.name as sub_satuan_kerja',
                    'ms_perwakilan.name as perwakilan',
                    'ms_perwakilan.type as perwakilan_type',

                )
                ->whereNotIn('email', ['techteam@gmail.com'])
                ->whereNull('users.deleted_at')
                ->whereNull('ms_satuan_kerja.deleted_at')
                ->whereNull('ms_sub_satuan_kerja.deleted_at');
            
            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['users.name', 'username', 'email', 'ms_roles.name', 'ms_satuan_kerja.name', 'ms_sub_satuan_kerja.name', 'ms_perwakilan.name', 'ms_perwakilan.type'], 'users.updated_at');

            return ApiResponse::success($data, 'Berhasil mengambil data pengguna');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $user = User::findOrFail($id);
            return ApiResponse::success($user, 'Berhasil mengambil data detail pengguna');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'email' => ['required', 'string', 'email', 'max:255', new UniqueSoftDelete('users', 'email')],
                'username' => ['required', 'string', 'max:100', new UniqueSoftDelete('users', 'username')],
                'name' => 'required|string|max:255',
                'password' => 'required|string|min:8|max:100',
                'role_id' => 'required|integer',
                'gender' => 'required|in:1,2',
                'phone_number' => 'nullable|string|max:50',
                'status' => 'required|integer'
            ]);

            $user = auth('admin')->user()->name;

            $role = MsRole::find($request->role_id);
            if (!$role) {
                DB::rollBack();
                return ApiResponse::error('Role tidak valid', '', 422);
            }
            if ($role->name == 'Admin Satker/Perwakilan') {
                $request->validate([
                    'satuan_kerja_id' => 'required_without:perwakilan_id|integer|nullable',
                    'sub_satuan_kerja_id' => 'required_with:satuan_kerja_id|integer|nullable',
                    'perwakilan_id' => 'required_without:satuan_kerja_id|integer|nullable',
                ]);
            }

            $data = new User();
            $data->email = $request->email;
            $data->username = $request->username;
            $data->name = $request->name;
            $data->password = bcrypt($request->password);
            $data->role_id = $request->role_id;
            $data->gender = $request->gender;
            $data->phone_number = $request->phone_number;
            $data->status = $request->status;
            $data->created_by = $user;
            $data->updated_by = $user;

            if (!in_array($role->name, ['Superadmin', 'Admin Pusat'])) {
                if ($request->filled('perwakilan_id')) {
                    $perwakilan = MsPerwakilan::find($request->perwakilan_id);
                    if (!$perwakilan) {
                        DB::rollBack();
                        return ApiResponse::error('Perwakilan tidak valid', '', 422);
                    }
                    $data->perwakilan_id = $request->perwakilan_id;
                    $data->satuan_kerja_id = null;
                    $data->sub_satuan_kerja_id = null;
                } else {
                    $data->perwakilan_id = null;
                    if ($request->filled('satuan_kerja_id')) {
                        $satuanKerja = MsSatuanKerja::find($request->satuan_kerja_id);
                        if (!$satuanKerja) {
                            DB::rollBack();
                            return ApiResponse::error('Satuan kerja tidak valid', '', 422);
                        }
                        $data->satuan_kerja_id = $request->satuan_kerja_id;
                    }
                    if ($request->filled('sub_satuan_kerja_id')) {
                        $subSatuanKerja = MsSubSatuanKerja::find($request->satuan_kerja_id);
                        if (!$subSatuanKerja) {
                            DB::rollBack();
                            return ApiResponse::error('Sub satuan kerja tidak valid', '', 422);
                        }
                        $data->sub_satuan_kerja_id = $request->sub_satuan_kerja_id;
                    }
                }
            }

            $data->save();

            LogActivity::create('pengguna', $data, 'create', auth('admin')->user());

            DB::commit();
            return ApiResponse::success($data, 'Berhasil menambahkan data pengguna', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = User::findOrFail($id);
            
            $request->validate([
                'email' => ['required', 'string', 'email', 'max:255', new UniqueSoftDelete('users', 'email', $id)],
                'username' => ['required', 'string', 'max:100', new UniqueSoftDelete('users', 'username', $id)],
                'name' => 'required|string|max:255',
                'password' => 'nullable|string|min:8|max:100',
                'role_id' => 'required|integer',
                'gender' => 'required|in:1,2',
                'phone_number' => 'nullable|string|max:50',
                'status' => 'required|integer'
            ]);

            $user = auth('admin')->user()->name;

            $role = MsRole::find($request->role_id);
            if (!$role) {
                DB::rollBack();
                return ApiResponse::error('Role tidak valid', '', 422);
            }
            if ($role->name == 'Admin Satker/Perwakilan') {
                $request->validate([
                    'satuan_kerja_id' => 'required_without:perwakilan_id|integer|nullable',
                    'sub_satuan_kerja_id' => 'required_with:satuan_kerja_id|integer|nullable',
                    'perwakilan_id' => 'required_without:satuan_kerja_id|integer|nullable',
                ]);
            }

            $data->email = $request->email;
            $data->username = $request->username;
            $data->name = $request->name;
            $data->role_id = $request->role_id;
            $data->gender = $request->gender;
            $data->phone_number = $request->phone_number;
            $data->status = $request->status;
            $data->updated_by = $user;

            if ($request->filled('password')) {
                $data->password = bcrypt($request->password);
            }

            if (in_array($role->name, ['Superadmin', 'Admin Pusat'])) {
                $data->perwakilan_id = null;
                $data->satuan_kerja_id = null;
                $data->sub_satuan_kerja_id = null;
            } else {
                if ($request->filled('perwakilan_id')) {
                    $perwakilan = MsPerwakilan::find($request->perwakilan_id);
                    if (!$perwakilan) {
                        DB::rollBack();
                        return ApiResponse::error('Perwakilan tidak valid', '', 422);
                    }
                    $data->perwakilan_id = $request->perwakilan_id;
                    $data->satuan_kerja_id = null;
                    $data->sub_satuan_kerja_id = null;
                } else {
                    $data->perwakilan_id = null;
                    if ($request->filled('satuan_kerja_id')) {
                        $satuanKerja = MsSatuanKerja::find($request->satuan_kerja_id);
                        if (!$satuanKerja) {
                            DB::rollBack();
                            return ApiResponse::error('Satuan kerja tidak valid', '', 422);
                        }
                        $data->satuan_kerja_id = $request->satuan_kerja_id;
                    }
                    if ($request->filled('sub_satuan_kerja_id')) {
                        $subSatuanKerja = MsSubSatuanKerja::find($request->satuan_kerja_id);
                        if (!$subSatuanKerja) {
                            DB::rollBack();
                            return ApiResponse::error('Sub satuan kerja tidak valid', '', 422);
                        }
                        $data->sub_satuan_kerja_id = $request->sub_satuan_kerja_id;
                    }
                }
            }

            $data->save();

            LogActivity::create('pengguna', $data, 'update', auth('admin')->user());

            DB::commit();
            return ApiResponse::success($data, 'Berhasil memperbarui data pengguna');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = User::findOrFail($id);
            $data->delete();

            LogActivity::create('pengguna', $data, 'delete', auth('admin')->user());

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data pengguna');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}