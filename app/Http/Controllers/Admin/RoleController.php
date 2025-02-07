<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Admins\Role;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{

    /**
     * * Function untuk mengambil semua data role
     *
     * @return ResponseResource
     */
    public function index(): ResponseResource
    {
        try {
            $roles = Role::all();

            if ($roles->isEmpty()) {
                return ResponseResource::notFound();
            }

            return ResponseResource::success('Data Berhasil Ditemukan', $roles);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }


    /**
     * * Function untuk menambahkan data role
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function store(\Illuminate\Http\Request $request): ResponseResource
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:roles,name',
            ]);

            if ($validator->fails()) {
                return ResponseResource::error($validator->errors());
            }

            $validated = $validator->validated();

            $role = Role::create($validated);

            return new ResponseResource([200, 'Data Berhasil Ditambahkan', $role]);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

    /**
     * * Function untuk menghapus data role berdasarkan id
     *
     * @param int $id
     * @return ResponseResource
     */
    public function destroy($id): ResponseResource
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return ResponseResource::notFound();
            }

            $role->delete();

            return ResponseResource::success('Role Berhasil Dihapus', null);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }
}
