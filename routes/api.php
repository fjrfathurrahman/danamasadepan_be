<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// * Route Role
Route::prefix('role')->controller(RoleController::class)->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::post('/add', 'store')->middleware('auth:sanctum');
    Route::delete('/{id}', 'destroy')->middleware('auth:sanctum');
});

// * Route Admin
Route::prefix('admin')->controller(AdminController::class)->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::post('/add', 'store')->middleware('auth:sanctum');
});

// * Route Auth
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'registerStudent');
    Route::post('/logout', 'logout')->middleware('auth:sanctum');
});

// * Route Siswa 
Route::prefix('student')->controller(StudentController::class)->group(function () {
    Route::get('/', 'index');
    Route::delete('/{id}', 'destroy')->middleware('auth:sanctum');
});