<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Route::view('dashboard', 'dashboard')
//    ->middleware(['auth'])
// //    ->middleware(['auth', 'verified'])
//    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Volt::route('dashboard', 'dashboard')->name('dashboard');
    Volt::route('notifications', 'notification')->name('notification');
    Volt::route('incomes', 'income')->name('income');

    Route::middleware(['can:isAdmin, App\Models\User'])->prefix('admin')->name('admin.')->group(function () {
        Volt::route('settings/general', 'settings.general')->name('settings.general');
        Volt::route('settings/question', 'settings.question')->name('settings.question');

        Volt::route('users/patient', 'admin.users.patient')->name('users.patient');
        Volt::route('users/doctor', 'admin.users.doctor')->name('users.doctor');
    });

    Route::middleware(['can:isDoctor, App\Models\User'])->group(function () {
        Route::redirect('settings', 'settings/profile');

        Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
        Volt::route('settings/password', 'settings.password')->name('settings.password');
        Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

        Route::prefix('doctor')->name('doctor.')->group(function () {
            Route::redirect('therapies', 'therapies/in-progress');

            Volt::route('therapies/in-progress', 'doctor.therapy.in_progress.index')->name('therapies.in_progress.index');
            Volt::route('therapies/in-progress/chat', 'doctor.therapy.in_progress.chat')->name('therapies.in_progress.chat');
            Volt::route('therapies/in-progress/schedule', 'doctor.therapy.in_progress.schedule')->name('therapies.in_progress.schedule');

            Volt::route('therapies/in-progress/records/sleep-diary', 'doctor.therapy.sleep_diary.index')->name('therapies.sleep_diary.index');
        });
    });

});

require __DIR__.'/auth.php';
