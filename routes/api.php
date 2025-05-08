<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CommittedActionController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\EmotionRecordController;
use App\Http\Controllers\Api\IdentifyValueController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\SleepDiaryController;
use App\Http\Controllers\Api\TherapyController;
use App\Http\Controllers\Api\TherapyScheduleController;
use App\Http\Controllers\Api\ThoughtRecordController;
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

Route::get('/problems', [PatientController::class, 'getProblems']);

Route::middleware(['auth:sanctum', 'verified.api'])->group(function () {
    Route::get('/patient/profile', function (Request $request) {
        return Response::success([
            'user' => new UserResource($request->user()),
        ], 'Berhasil mengambil data profile pasien.');
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/doctors', [DoctorController::class, 'getAll']);
    Route::get('/doctors/{id}', [DoctorController::class, 'getById']);

    Route::put('/patient/profile', [PatientController::class, 'updateProfile']);
    Route::put('/patient/password', [PatientController::class, 'updatePassword']);

    Route::get('/therapies', [TherapyController::class, 'get']);

    Route::prefix('therapy')->group(function () {
        Route::get('/schedules', [TherapyScheduleController::class, 'get']);

        Route::get('/chat', [ChatController::class, 'get']);
        Route::post('/chat', [ChatController::class, 'send']);

        Route::prefix('records')->group(function () {
            Route::get('/sleep-diaries', [SleepDiaryController::class, 'get']);
            Route::get('/sleep-diaries/{id}', [SleepDiaryController::class, 'getDetail']);

            Route::get('/though-records', [ThoughtRecordController::class, 'get']);
            Route::get('/identify-values', [IdentifyValueController::class, 'get']);
            Route::get('/emotion-records', [EmotionRecordController::class, 'get']);
            Route::get('/committed-actions', [CommittedActionController::class, 'get']);

            Route::get('/questions', [QuestionController::class, 'get']);
        });
    });
});
