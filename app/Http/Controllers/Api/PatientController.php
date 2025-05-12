<?php

namespace App\Http\Controllers\Api;

use App\Enum\Problem;
use App\Enum\UserGender;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class PatientController extends Controller
{
    public function getProblems()
    {
        try {

            $problems = array_map(fn ($case) => $case->value, Problem::cases());

            return Response::success([
                'problems' => $problems,
            ], 'Berhasil mendapatkan daftar masalah.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getProfile(Request $request)
    {
        try {

            return Response::success(new UserResource($request->user()),
                'Berhasil mengambil data profile pasien.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:225'],
            'age' => ['required', 'integer', 'min:1', 'max:100'],
            'gender' => ['required', new Enum(UserGender::class)],
            'problems' => ['nullable', 'array'],
            'problems.*' => ['string', new Enum(Problem::class)],
        ]);

        try {

            if (! empty($validated['problems'])) {
                $validated['problems'] = json_encode($validated['problems']);
            }
            $request->user()->update($validated);

            return Response::success(new UserResource($request->user()), 'Berhasil mengubah data profile.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'max:225'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'max:225', 'different:current_password'],
        ]);

        try {

            if (! Hash::check($validated['current_password'], $request->user()->password)) {
                return Response::error('Password saat ini salah.', 500);
            }

            if (Hash::check($validated['new_password'], $request->user()->password)) {
                return Response::error('Password baru tidak boleh sama dengan password lama.', 500);
            }

            $request->user()->update([
                'password' => Hash::make($validated['new_password']),
            ]);

            return Response::success(null, 'Password berhasil diubah.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
