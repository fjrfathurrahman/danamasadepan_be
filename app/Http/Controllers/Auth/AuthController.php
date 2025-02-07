<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Admins\Admin;
use App\Models\Students\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * * Function Login User
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return ResponseResource::error($validator->errors());
            }

            $validated = $validator->validated();

            // Cek apakah user adalah admin atau student menggunakan Query DB
            $admin = DB::table('admins')->where('email', $validated['email'])->first();
            $student = DB::table('students')->where('email', $validated['email'])->first();

            if (!$admin && !$student) {
                return ResponseResource::notFound();
            }

            // Login sebagai Admin
            if ($admin && Hash::check($validated['password'], $admin->password)) {
                $adminModel = Admin::find($admin->id); // Menemukan admin berdasarkan ID 
                $token = $adminModel->createToken('AdminToken')->plainTextToken;

                return ResponseResource::success('Login Berhasil', ['token' => $token, 'role' => 'admin', 'user' => $admin]);
            }

            // Login sebagai Student
            if ($student && Hash::check($validated['password'], $student->password)) {
                $studentModel = Student::find($student->id); // Menemukan student berdasarkan ID
                $token = $studentModel->createToken('StudentToken')->plainTextToken;

                return ResponseResource::success('Login Berhasil', [ 'token' => $token, 'role' => 'student', 'user' => $student]);
            }

            return ResponseResource::error('Email atau Password Tidak Sesuai');
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }


    /**
     * * Function Logout User
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function logout(Request $request): ResponseResource
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return ResponseResource::success('Logout Berhasil', null);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }


    /**
     * * Function untuk mendaftarkan siswa baru
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function registerStudent(Request $request): ResponseResource
    {
        try {
            $validator = Validator::make($request->all(), ([
                'name' => 'required|string',
                'email' => 'required|email|unique:students,email',
                'password' => 'required|min:6',
                'class' => 'required|string',
                'major' => 'required|string',
                'gender' => 'required|string',
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif',
            ]));

            if ($validator->fails()) {
                return ResponseResource::error($validator->errors());
            };

            $validated = $validator->validate();
            $validated['password'] = Hash::make($validated['password']);

            if ($request->hasFile('photo')) {
                $validated['photo'] = $request->file('photo')->store('student_photos', 'public');
            }

            $student = Student::create($validated);

            // Commit transaksi jika semua sukses
            DB::commit();

            return ResponseResource::success( 'Siswa Berhasil Didaftarkan', $student);
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseResource::error($th->getMessage());
        }
    }
}
