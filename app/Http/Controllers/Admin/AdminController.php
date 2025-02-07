<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Admins\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    /**
     * * Function untuk mengambil semua data admin.
     *
     * @return ResponseResource
     */
    public function index(): ResponseResource
    {
        try {
            $admins = Admin::all();

            if ($admins->isEmpty()) {
                return new ResponseResource([404, 'Admin Tidak Ditemukan', null]);
            }

            return new ResponseResource([200, 'Admin Ditemukan', $admins]);
        } catch (\Throwable $th) {
            return new ResponseResource([505, 'Internal Server Error', $th->getMessage()]);
        }
    }

    /**
     * Function untuk membuat admin baru.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function store(Request $request): ResponseResource
    {
        try {
            
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:admins,email',
                'password' => 'required|min:6',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif',
                'role_id' => 'required|exists:roles,id', 
            ]);

            $validated['password'] = Hash::make($request->password);

            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('admin_photos', 'public');
            }

            $admin = Admin::create($validated);

            return new ResponseResource([200, 'Admin Berhasil Ditambahkan', $admin]);
        } catch (\Throwable $th) {
            return new ResponseResource([505, 'Internal Server Error', $th->getMessage()]);
        }
    }
}
