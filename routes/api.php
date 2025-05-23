<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\MidtransController;
use App\Http\Controllers\Api\OrderController;
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
Route::get('areas', [RecordController::class, 'getIdentifyValueArea']);
Route::get('emotions', [RecordController::class, 'getEmotionRecordEmotion']);

Route::prefix('midtrans')->group(function () {
    Route::post('/charge', [MidtransController::class, 'charge']);
    Route::post('/notification', [MidtransController::class, 'notification']);
});

Route::middleware(['auth:sanctum', 'verified.api'])->group(function () {

    Route::get('/patient/profile', [PatientController::class, 'getProfile']);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/doctors', [DoctorController::class, 'getAll']);
    Route::get('/doctors/{id}', [DoctorController::class, 'getById']);

    Route::put('/patient/profile', [PatientController::class, 'updateProfile']);
    Route::put('/patient/password', [PatientController::class, 'updatePassword']);

    Route::get('/therapies', [TherapyController::class, 'get']);

    Route::prefix('therapy')->group(function () {
        Route::post('/rating', [TherapyController::class, 'storeRating']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'get']);

        Route::get('/schedules/{id}', [TherapyScheduleController::class, 'get']);

        Route::get('/chats', [ChatController::class, 'get']);
        Route::post('/chats', [ChatController::class, 'send']);
        Route::put('/chats/{id}', [ChatController::class, 'update']);

        Route::prefix('records')->group(function () {

            Route::get('/sleep-diaries', [RecordController::class, 'getSleepDiaries']);
            Route::get('/sleep-diaries/{id}', [RecordController::class, 'getSleepDiaryByID']);

            Route::get('/thought-records', [RecordController::class, 'getThoughtRecord']);
            Route::post('/thought-records', [RecordController::class, 'storeThoughtRecord']);

            Route::get('/identify-values', [RecordController::class, 'getIdentifyValue']);

            Route::get('/emotion-records', [RecordController::class, 'getEmotionRecord']);
            Route::post('/emotion-records', [RecordController::class, 'storeEmotionRecord']);

            Route::get('/committed-actions', [RecordController::class, 'getCommittedAction']);
            Route::post('/committed-actions', [RecordController::class, 'storeCommittedAction']);

            Route::get('/questions', [QuestionController::class, 'get']);
            Route::get('/answers', [RecordController::class, 'get']);
            Route::post('/answers', [RecordController::class, 'store']);
            Route::put('/answers', [RecordController::class, 'update']);
        });
    });
});
