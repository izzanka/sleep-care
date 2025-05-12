<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\MidtransController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\RecordController;
use App\Http\Controllers\Api\TherapyController;
use App\Http\Controllers\Api\TherapyScheduleController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::post('/otp/request', [OtpController::class, 'request']);
Route::post('/otp/verify', [OtpController::class, 'verify']);

Route::get('/problems', [PatientController::class, 'getProblems']);

Route::middleware(['auth:sanctum', 'verified.api'])->group(function () {
    Route::post('/charge', [MidtransController::class, 'charge']);

    Route::get('/patient/profile', [PatientController::class, 'getProfile']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/doctors', [DoctorController::class, 'getAll']);
    Route::get('/doctors/{id}', [DoctorController::class, 'getById']);

    Route::put('/patient/profile', [PatientController::class, 'updateProfile']);
    Route::put('/patient/password', [PatientController::class, 'updatePassword']);

    Route::get('/therapies', [TherapyController::class, 'get']);

    Route::prefix('therapy')->group(function () {
        Route::post('/order', [TherapyController::class, 'order']);

        Route::get('/schedules', [TherapyScheduleController::class, 'get']);

        Route::get('/chat', [ChatController::class, 'get']);
        Route::post('/chat', [ChatController::class, 'send']);

        Route::prefix('records')->group(function () {
            Route::get('/sleep-diaries', [RecordController::class, 'getSleepDiaries']);
            Route::get('/sleep-diaries/{id}', [RecordController::class, 'getSleepDiaryByID']);

            Route::get('/thought-records', [RecordController::class, 'getThoughtRecords']);
            Route::get('/identify-values', [RecordController::class, 'getIdentifyValues']);
            Route::get('/emotion-records', [RecordController::class, 'getEmotionRecords']);
            Route::get('/committed-actions', [RecordController::class, 'getCommittedActions']);

            Route::get('/questions', [QuestionController::class, 'get']);
        });
    });
});
