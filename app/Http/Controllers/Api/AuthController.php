<?php

namespace App\Http\Controllers\Api;

use App\Enum\Problem;
use App\Enum\UserGender;
use App\Enum\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\ResetPasswordOtpMail;
use App\Models\User;
use App\Service\TokenService;
use App\Service\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Enum;

class AuthController extends Controller
{
    public function __construct(protected UserService $userService, protected TokenService $otpService) {}

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:225'],
            'password' => ['required', 'string', 'max:225'],
        ]);

        try {
            $user = $this->userService->get(email: $validated['email'], role: UserRole::PATIENT->value)->first();

            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return Response::error('Email atau password yang anda masukan salah.', 401);
            }

            if (! $user->is_active) {
                return Response::error('Akun ini telah dinonaktifkan oleh admin.', 400);
            }

            if (is_null($user->email_verified_at)) {
                return Response::error('Email akun belum diverifikasi.', 401);
            }

            $user->update(['is_online' => true]);
            $token = $user->createToken('auth_token')->plainTextToken;

            return Response::success([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ], 'Login berhasil.');

        } catch (Exception $exception) {
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
            ], 'Register berhasil.');

        } catch (Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->update(['is_online' => false]);
            $user->currentAccessToken()->delete();

            return Response::success(null, 'Logout sukses.');
        } catch (Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        try {
            $user = $this->userService->get(email: $validated['email'], role: UserRole::PATIENT->value, verified: true, is_active: true)->first();
            if (! $user) {
                return Response::error('Akun tidak ditemukan.', 404);
            }

            $checkOtp = $this->otpService->get($validated['email']);
            if ($checkOtp) {
                if (now()->greaterThan($checkOtp->expired_at)) {
                    $this->otpService->delete(email: $validated['email']);
                } else {
                    return Response::error('Kode OTP sudah dikirim sebelumnya dan belum kedaluwarsa.', 400);
                }
            }

            $otp = $this->otpService->generate();
            $this->otpService->store($validated['email'], $otp);

            Mail::to($validated['email'])->send(new ResetPasswordOtpMail($otp));

            return Response::success(null, 'Kode OTP berhasil dikirimkan.');

        } catch (Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:225'],
            'token' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'max:225'],
        ]);

        try {

            $otpRecord = $this->otpService->get($validated['email'], $validated['token']);
            if (! $otpRecord || now()->greaterThan($otpRecord->expired_at)) {
                return Response::error('Kode OTP tidak valid atau sudah kedaluwarsa.', 422);
            }

            $user = $this->userService->get(email: $validated['email'], role: UserRole::PATIENT->value, verified: true, is_active: true)->first();
            if (! $user) {
                return Response::error('Akun tidak ditemukan.', 404);
            }

            $user->password = Hash::make($validated['password']);
            $user->setRememberToken(str()->random(60));
            $user->save();

            $this->otpService->delete($validated['token'], $validated['email']);

            return Response::success(null, 'Password berhasil direset.');

        } catch (Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
