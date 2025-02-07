<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Admins\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    
    /**
     * Function Login User
     *
     * @param  \Illuminate\Http\Request $request
     * @return \App\Http\Resources\ResponseResource
     */
    public function login(Request $request): ResponseResource
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            $user = Admin::where('email', $validated['email'])->first();

            if (!$user) {
                return new ResponseResource([404, 'User Tidak Ditemukan', null]);
            }

            if (!Hash::check($validated['password'], $user->password)) {
                return new ResponseResource([401, 'Password Tidak Sesuai Dengan User', null]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return new ResponseResource([200, 'Login Berhasil', ['token' => $token]]);
        } catch (\Throwable $th) {
            return new ResponseResource([505, 'Internal Server Error', null]);
        }
    }
}
