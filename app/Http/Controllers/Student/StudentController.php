<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Students\Student;

class StudentController extends Controller
{
    /**
     * * Mengambil semua data siswa.
     * 
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
                    'created_at' => $student->created_at->toIso8601String(),
                    'updated_at' => $student->updated_at->toIso8601String()
                ];
            });
    
            return ResponseResource::success('Data Berhasil Ditemukan', $formattedStudents);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }

}
