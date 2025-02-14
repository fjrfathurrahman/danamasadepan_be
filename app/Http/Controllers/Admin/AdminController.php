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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            $admins = Admin::with('role')->get();

            if ($admins->isEmpty()) {
                return ResponseResource::notFound();
            }

            return ResponseResource::success('Admin Berhasil Ditemukan', $admins);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

    /**
     * * Function untuk mengambil profil admin yang sedang login.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function profile(): ResponseResource
    {
        try {
            $admin = Auth::user();

            if (!$admin) {
                return ResponseResource::notFound();
            }

            return ResponseResource::success('Profile Admin Berhasil Ditemukan', $admin);
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

            if ($validated['role_id'] == 1) {
                $superAdminExists = Admin::where('role_id', 1)->exists();

                if ($superAdminExists) {
                    return ResponseResource::error('Hanya boleh ada satu role Super Admin', 403);
                }
            }
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
     * * Function untuk mengambil data admin berdasarkan id
     *
     * @return \App\Http\Resources\ResponseResource
     */
    public function show(Request $request): ResponseResource
    {
        try {
            $admin = Admin::findOrFail($request->id);

            return ResponseResource::success('Admin Berhasil Ditemukan', $admin);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

    /**
     * * Function untuk menghapus data admin berdasarkan id, termasuk foto di storage.
     *
     * @param int $id
     * @return \App\Http\Resources\ResponseResource
     */
    public function destroy(Request $request): ResponseResource
    {
        try {
            $admin = Admin::findOrFail($request->id);

            if ($admin->role_id === 1) {
                return ResponseResource::error('Super Admin tidak bisa dihapus', 403);
            }

            if ($admin->photo) {
                $photoPath = $admin->photo;

                if (Storage::disk('public')->exists($photoPath)) {
                    Storage::disk('public')->delete($photoPath);
                }
            }

            // Hapus admin dari database
            $admin->delete();

            return ResponseResource::success('Admin Berhasil Dihapus');
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }


    /**
     * * Function untuk memperbarui data admin berdasarkan id.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function update(Request $request, $id): ResponseResource
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string',
                'email' => 'sometimes|email',
                'password' => 'sometimes|min:8',
                'role_id' => 'sometimes|exists:roles,id',
                'photo' => $request->hasFile('photo') ? 'image|mimes:jpeg,png,jpg,gif' : '',
            ]);

            if ($validator->fails()) {
                return ResponseResource::error($validator->errors()->first());
            }

            // Cari admin berdasarkan ID
            $admin = Admin::findOrFail($id);

            // Siapkan data yang akan diupdate
            $dataToUpdate = [];

            if ($request->filled('name')) {
                $dataToUpdate['name'] = $request->name;
            }
            if ($request->filled('email')) {
                $dataToUpdate['email'] = $request->email;
            }
            if ($request->filled('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
            }
            if ($request->filled('role_id')) {
                $dataToUpdate['role_id'] = $request->role_id;
            }

            // Jika ada file photo yang diunggah
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');

                if ($file->isValid()) {

                    // Hapus foto lama jika ada
                    if ($admin->photo) {
                        Storage::disk('public')->delete($admin->photo);
                    }

                    // Simpan foto baru dan dapatkan path-nya
                    $path = $file->store('admin_photos', 'public');
                    if ($path) {
                        $dataToUpdate['photo'] = $path;
                    }
                }
            }

            // Lakukan update
            $admin->update($dataToUpdate);

            return ResponseResource::success('Admin berhasil diperbarui', $admin->refresh());
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

    // Tambahkan function baru di AdminController untuk toggle status allowed student

    public function updateAllowed(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Cari siswa berdasarkan ID
            $student = Student::findOrFail($id);

            // Toggle status allowed
            $student->allowed = !$student->allowed;
            $student->save();

            DB::commit();
            return ResponseResource::success(
                'Status Siswa Berhasil Diperbarui',
                [
                    'student' => $student,
                    'message' => "Status menabung siswa {$student->name} telah " . ($student->allowed ? 'diaktifkan' : 'dinonaktifkan')
                ]
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseResource::error($th->getMessage());
        }
    }
}
