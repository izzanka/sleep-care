<?php

namespace App\Http\Controllers\Api;

use App\Enum\Problem;
use App\Enum\UserGender;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Service\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class PatientController extends Controller
{
    public function __construct(protected UserService $userService) {}

    public function getProblems()
    {
        try {

            $problems = array_map(fn ($case) => $case->value, Problem::cases());

            return Response::success([
                'problems' => $problems,
            ], 'Get patient problems success.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:225'],
            'age' => ['required', 'integer', 'min:1', 'max:100'],
            'gender' => ['required', new Enum(UserGender::class)],
            'problems' => ['nullable', 'array'],
            'problems.*' => ['string', new Enum(Problem::class)],
        ]);

        try {

            $updateUser = $request->user()->update($validated);
            if (! $updateUser) {
                return Response::error('Update profile patient failed.', 500);
            }

            return Response::success([
                'user' => new UserResource($request->user()),
            ], 'Update profile patient success.');

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
                return Response::error('Current password is incorrect.', 500);
            }

            if (Hash::check($validated['new_password'], $request->user()->password)) {
                return Response::error('New password cannot be the same as the current password.', 500);
            }

            $updatePassword = $request->user()->update([
                'password' => Hash::make($validated['new_password']),
            ]);

            if (! $updatePassword) {
                return Response::error('Update password patient failed.', 500);
            }

            return Response::success(null, 'Update password patient success.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
