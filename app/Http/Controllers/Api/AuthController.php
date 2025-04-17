<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return Response::error('The provided credentials are incorrect.', 401);
        }

        if (is_null($user->email_verified_at)) {
            return Response::error('Patient account is not verified.', 401);
        }

        if (!$user->is_active) {
            return Response::error('Patient account is inactive.', 403);
        }

        $user->update(['is_online' => true]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return Response::success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 'Login successful.');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'age' => ['required', 'integer', 'min:1', 'max:100'],
            'gender' => ['required', 'string', 'in:pria,perempuan'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'age' => $validated['age'],
            'gender' => $validated['gender'],
        ]);

        return Response::success([
            'user' => $user,
        ], 'Patient registered successfully.');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->update(['is_online' => false]);
        $user->currentAccessToken()->delete();

        return Response::success(null, 'Logout successful.');
    }
}
