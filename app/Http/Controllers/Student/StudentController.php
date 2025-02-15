<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Students\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * * Mengambil semua data siswa.
     */
    public function index(): ResponseResource
    {
        try {
            $students = Student::all();

            if ($students->isEmpty()) {
                return ResponseResource::notFound();
            }

            $formattedStudents = $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'gender' => $student->gender,
                    'class' => $student->class,
                    'major' => $student->major,
                    'phone' => $student->phone,
                    'address' => $student->address,
                    'photo' => $student->photo,
                    'balance' => number_format($student->balance, 0, ',', '.'),
                    'allowed' => $student->allowed,
                    'created_at' => $student->created_at,
                    'updated_at' => $student->updated_at
                ];
            });

            return ResponseResource::success('Data Berhasil Ditemukan', $formattedStudents);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

    /**
     * * Function untuk mengambil data student berdasarkan id
     */
    public function show(Request $request, $id)
    {
        try {
            // Cari student berdasarkan ID
            $student = Student::with('admin')->findOrFail($request->id);

            if (!$student) {
                return ResponseResource::notFound();
            }

            return ResponseResource::success('Data Berhasil Ditemukan', $student);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * * Function untuk memperbarui data siswa berdasarkan id.
     */
     //REVIEW - UPDATE SISWA
    public function update(Request $request, $id): ResponseResource
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string',
                'email' => 'sometimes|email',
                'phone' => 'sometimes|string',
                'address' => 'sometimes|string',
                'major' => 'sometimes|string|in:TKJ,RPL,AKL',
                'class' => 'sometimes|string|in:X,XI,XII',
                'gender' => 'sometimes|string|in:Laki-laki,Perempuan',
                'photo' => $request->hasFile('photo') ? 'image|mimes:jpeg,png,jpg,gif' : '',
            ]);

            if ($validator->fails()) {
                return ResponseResource::error($validator->errors()->first());
            }

            // Cari student berdasarkan ID
            $student = Student::findOrFail($id);

            // Siapkan data yang akan diupdate
            $dataToUpdate = [];

            if ($request->filled('name')) {
                $dataToUpdate['name'] = $request->name;
            }

            if ($request->filled('email')) {
                $dataToUpdate['email'] = $request->email;
            }

            if ($request->filled('phone')) {
                $dataToUpdate['phone'] = $request->phone;
            }

            if ($request->filled('address')) {
                $dataToUpdate['address'] = $request->address;
            }

            if ($request->filled('major')) {
                $dataToUpdate['major'] = $request->major;
            }

            if ($request->filled('class')) {
                $dataToUpdate['class'] = $request->class;
            }

            if ($request->filled('gender')) {
                $dataToUpdate['gender'] = $request->gender;
            }

            // Jika ada file photo yang diunggah
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');

                if ($file->isValid()) {

                    // Hapus foto lama jika ada
                    if ($student->photo) {
                        Storage::disk('public')->delete($student->photo);
                    }

                    // Simpan foto baru dan dapatkan path-nya
                    $path = $file->store('student_photos', 'public');
                    if ($path) {
                        $dataToUpdate['photo'] = $path;
                    }
                }
            }

            // Lakukan update
            $student->update($dataToUpdate);

            return ResponseResource::success('Siswa Berhasil Diperbarui', $student->refresh());
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }
}
