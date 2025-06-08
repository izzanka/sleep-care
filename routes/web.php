<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');
Route::get('/download', [HomeController::class, 'download'])->name('download');

Route::prefix('payment')->group(function () {
    Route::get('finish', function () {
        return view('midtrans.finish');
    });
    Route::get('error', function () {
        return view('midtrans.error');
    });
});

Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('dashboard', 'dashboard')->name('dashboard');
    Volt::route('notifications', 'notification')->name('notification');
    Volt::route('incomes', 'income')->name('income');

    Route::middleware(['can:isAdmin, App\Models\User'])->prefix('admin')->name('admin.')->group(function () {
        Volt::route('settings/general', 'settings.general')->name('settings.general');
        Volt::route('settings/question', 'settings.question')->name('settings.question');

        Volt::route('users/patient', 'admin.users.patient')->name('users.patient');
        Volt::route('users/doctor', 'admin.users.doctor')->name('users.doctor');
        //        Volt::route('therapies', 'admin.therapies')->name('therapies');
    });

    Route::middleware(['can:isDoctor, App\Models\User'])->group(function () {
        Route::redirect('settings', 'settings/profile');

        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
        Volt::route('settings/password', 'settings.password')->name('settings.password');
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

        Route::prefix('doctor/therapies')->name('doctor.')->group(function () {
            Volt::route('in-progress', 'doctor.therapy.in_progress.index')->name('therapies.in_progress.index');
            Volt::route('in-progress/{therapyId}', 'doctor.therapy.in_progress.detail')->name('therapies.in_progress.detail');

            Volt::route('completed', 'doctor.therapy.completed.index')->name('therapies.completed.index');
            Volt::route('completed/{therapyId}', 'doctor.therapy.completed.detail')->name('therapies.completed.detail');
        });
    });
});

require __DIR__.'/auth.php';
