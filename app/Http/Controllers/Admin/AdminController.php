<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Admins\Admin;
use App\Models\Students\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                return ResponseResource::notFound();
            }

            return ResponseResource::success('Admin Berhasil Ditemukan', $admins);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

    /**
     * * Function untuk membuat admin baru.
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

            return ResponseResource::success('Admin Berhasil Ditambahkan', $admin);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

    /**
     * * Function untuk mengambil data admin yang sedang login.
     *
     * @return \App\Http\Resources\ResponseResource
     */
    public function show()
    {
        try {
            $admin = Auth::user();

            return ResponseResource::success('Admin Berhasil Ditemukan', $admin);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

    /**
     * * Function untuk mengaktifkan atau menonaktifkan siswa menabung.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function updateAllowed(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'id' => 'required|exists:students,id',
                'allowed' => 'required|boolean'
            ]);

            // Cari dan update status siswa
            $student = Student::findOrFail($validated['id']);
            $student->update([
                'allowed' => $validated['allowed']
            ]);

            DB::commit();
            return ResponseResource::success(
                'Status siswa berhasil diperbarui', 
                [
                    'student' => $student,
                    'message' => "Status menabung siswa {$student->name} telah " . ($validated['allowed'] ? 'diaktifkan' : 'dinonaktifkan')
                ]
            );

        } catch (\Throwable $th) {
            DB::rollback();
            return ResponseResource::error($th->getMessage());
        }
    }
}
