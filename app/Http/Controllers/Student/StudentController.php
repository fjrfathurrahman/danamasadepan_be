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

            return ResponseResource::success('Data Berhasil Ditemukan', $students);
        } catch (\Throwable $th) {
            return ResponseResource::error($th->getMessage());
        }
    }
}
