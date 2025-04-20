<?php

namespace App\Http\Controllers\Api;

use App\Enum\ModelFilter;
use App\Enum\Problem;
use App\Enum\UserGender;
use App\Enum\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Service\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;

class AuthController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:225'],
            'password' => ['required', 'string', 'max:225'],
        ]);

        try {
            $filters = [
                [
                    'operation' => ModelFilter::EQUAL->name,
                    'column' => 'email',
                    'value' => $validated['email'],
                ],
                [
                    'operation' => ModelFilter::EQUAL->name,
                    'column' => 'role',
                    'value' => UserRole::PATIENT->value,
                ],
            ];

            $user = $this->userService->get($filters)[0] ?? null;

            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return Response::error('The provided credentials are incorrect.', 401);
            }

            if (is_null($user->email_verified_at)) {
                return Response::error('Patient account is not verified.', 401);
            }

            $updateOnlineStatus = $user->update(['is_online' => true]);
            if (! $updateOnlineStatus) {
                Log::warning('Failed to update user online status');
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return Response::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ], 'Login successful.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'max:225'],
            'age' => ['required', 'integer', 'min:1', 'max:100'],
            'gender' => ['required', new Enum(UserGender::class)],
            'problems' => ['nullable', 'array'],
            'problems.*' => ['string', new Enum(Problem::class)],
        ]);

        try {

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'age' => $validated['age'],
                'gender' => $validated['gender'],
                'problems' => json_encode($validated['problems']),
            ]);

            return Response::success([
                'user' => new UserResource($user),
            ], 'Patient registered successfully.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            $updateOnlineStatus = $user->update(['is_online' => false]);
            if (! $updateOnlineStatus) {
                Log::warning('Failed to update user online status');
            }

            $user->currentAccessToken()->delete();

            return Response::success(null, 'Logout successful.');
        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
