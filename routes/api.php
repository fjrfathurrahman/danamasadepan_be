<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Other\OtherController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Transaction\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// * Route Auth
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'registerStudent');
    Route::post('reset-password', 'resetPassword')->middleware('auth:sanctum');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});

// * Route Role
Route::prefix('role')->controller(RoleController::class)->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::post('add', 'store')->middleware('auth:sanctum');
    Route::delete('delete/{id}', 'destroy')->middleware('auth:sanctum');
});

// * Route Admin
Route::prefix('admin')->controller(AdminController::class)->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('profile', 'profile')->middleware('auth:sanctum');
    Route::get('detail/{id}', 'show')->middleware('auth:sanctum');
    Route::post('add', 'store');
    Route::put('update/{id}', 'update')->middleware('auth:sanctum');
    Route::put('update/{id}/status', 'updateAllowed')->middleware('auth:sanctum');
    // Route::put('update/status', 'updateAllowed')->middleware('auth:sanctum');
    Route::delete('delete/{id}', 'destroy')->middleware('auth:sanctum');
});


// * Route Siswa
Route::prefix('student')->controller(StudentController::class)->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::put('update/{id}', 'update')->middleware('auth:sanctum');
    Route::get('detail/{id}', 'show')->middleware('auth:sanctum');
    Route::delete('delete/{id}', 'destroy')->middleware('auth:sanctum');
});


// * Route Transaksi
Route::prefix('transaction')->controller(TransactionController::class)->group(function () {
    Route::get('/', 'index')->middleware('auth:sanctum');
    Route::get('/user/{id}/{role}', 'indexByUser')->middleware('auth:sanctum');
    Route::get('weekly', 'getWeeklyTransactions')->middleware('auth:sanctum');
    Route::post('handle', 'store')->middleware('auth:sanctum');
    // Route::post('handle', 'handleTransaction')->middleware('auth:sanctum');
});

// * Route Other 
Route::get('/overview', [OtherController::class, 'overview']);  