<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::post('/otp/request', [OtpController::class, 'request']);
Route::post('/otp/verify', [OtpController::class, 'verify']);

Route::get('/patient/problems', [PatientController::class, 'getProblems']);

Route::middleware(['auth:sanctum', 'verified.api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/doctors', [DoctorController::class, 'get']);
    Route::get('/doctors/find', [DoctorController::class, 'find']);

    Route::get('/patient', function (Request $request) {
        return Response::success([
            'user' => new UserResource($request->user()),
        ], 'Get patient success.');
    });
    Route::put('/patient/profile', [PatientController::class, 'updateProfile']);
    Route::put('/patient/password', [PatientController::class, 'updatePassword']);
});
