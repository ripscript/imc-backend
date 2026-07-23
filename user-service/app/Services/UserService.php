<?php

namespace App\Services;

use App\Exceptions\Domain\InvalidRoleException;
use App\Models\MsPerwakilan;
use App\Models\MsRole;
use App\Models\MsSatuanKerja;
use App\Models\MsSubSatuanKerja;
use App\Models\User;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\UserDTO;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
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

        return $this->queryService->applyFiltersAndPaginate($query, $filter, [
            'users.name', 'username', 'email', 'ms_roles.name',
            'ms_satuan_kerja.name', 'ms_sub_satuan_kerja.name',
            'ms_perwakilan.name', 'ms_perwakilan.type',
        ], 'users.updated_at');
    }

    public function detail($id)
    {
        return User::findOrFail($id);
    }

    public function create(UserDTO $dto)
    {
        DB::beginTransaction();
        try {
            $role = MsRole::find($dto->role_id);
            if (! $role) {
                throw new InvalidRoleException('Role tidak valid');
            }

            $data = new User;
            $data->email = $dto->email;
            $data->username = $dto->username;
            $data->name = $dto->name;
            $data->password = bcrypt($dto->password);
            $data->role_id = $dto->role_id;
            $data->gender = $dto->gender;
            $data->phone_number = $dto->phone_number;
            $data->status = $dto->status;
            $data->created_by = $dto->created_by;
            $data->updated_by = $dto->updated_by;

            if (! in_array($role->name, ['Superadmin', 'Admin Pusat'])) {
                if ($dto->perwakilan_id) {
                    $perwakilan = MsPerwakilan::find($dto->perwakilan_id);
                    if (! $perwakilan) {
                        throw new InvalidRoleException('Perwakilan tidak valid');
                    }
                    $data->perwakilan_id = $dto->perwakilan_id;
                    $data->satuan_kerja_id = null;
                    $data->sub_satuan_kerja_id = null;
                } else {
                    $data->perwakilan_id = null;
                    if ($dto->satuan_kerja_id) {
                        $satuanKerja = MsSatuanKerja::find($dto->satuan_kerja_id);
                        if (! $satuanKerja) {
                            throw new InvalidRoleException('Satuan kerja tidak valid');
                        }
                        $data->satuan_kerja_id = $dto->satuan_kerja_id;
                    }
                    if ($dto->sub_satuan_kerja_id) {
                        $subSatuanKerja = MsSubSatuanKerja::find($dto->sub_satuan_kerja_id);
                        if (! $subSatuanKerja) {
                            throw new InvalidRoleException('Sub satuan kerja tidak valid');
                        }
                        $data->sub_satuan_kerja_id = $dto->sub_satuan_kerja_id;
                    }
                }
            }

            $data->save();
            DB::commit();

            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, UserDTO $dto)
    {
        DB::beginTransaction();
        try {
            $data = User::findOrFail($id);

            $role = MsRole::find($dto->role_id);
            if (! $role) {
                throw new InvalidRoleException('Role tidak valid');
            }

            $data->email = $dto->email;
            $data->username = $dto->username;
            $data->name = $dto->name;
            $data->role_id = $dto->role_id;
            $data->gender = $dto->gender;
            $data->phone_number = $dto->phone_number;
            $data->status = $dto->status;
            $data->updated_by = $dto->updated_by;

            if ($dto->password) {
                $data->password = bcrypt($dto->password);
            }

            if (in_array($role->name, ['Superadmin', 'Admin Pusat'])) {
                $data->perwakilan_id = null;
                $data->satuan_kerja_id = null;
                $data->sub_satuan_kerja_id = null;
            } else {
                if ($dto->perwakilan_id) {
                    $perwakilan = MsPerwakilan::find($dto->perwakilan_id);
                    if (! $perwakilan) {
                        throw new InvalidRoleException('Perwakilan tidak valid');
                    }
                    $data->perwakilan_id = $dto->perwakilan_id;
                    $data->satuan_kerja_id = null;
                    $data->sub_satuan_kerja_id = null;
                } else {
                    $data->perwakilan_id = null;
                    if ($dto->satuan_kerja_id) {
                        $satuanKerja = MsSatuanKerja::find($dto->satuan_kerja_id);
                        if (! $satuanKerja) {
                            throw new InvalidRoleException('Satuan kerja tidak valid');
                        }
                        $data->satuan_kerja_id = $dto->satuan_kerja_id;
                    }
                    if ($dto->sub_satuan_kerja_id) {
                        $subSatuanKerja = MsSubSatuanKerja::find($dto->sub_satuan_kerja_id);
                        if (! $subSatuanKerja) {
                            throw new InvalidRoleException('Sub satuan kerja tidak valid');
                        }
                        $data->sub_satuan_kerja_id = $dto->sub_satuan_kerja_id;
                    }
                }
            }

            $data->save();
            DB::commit();

            return $data;
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
            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
