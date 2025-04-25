<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
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

            Volt::route('therapies/in-progress/records/sleep-diary', 'doctor.therapy.records.sleep_diary')->name('therapies.records.sleep_diary');
            Volt::route('therapies/in-progress/records/identify-value', 'doctor.therapy.records.identify_value')->name('therapies.records.identify_value');
            Volt::route('therapies/in-progress/records/thought-record', 'doctor.therapy.records.thought_record')->name('therapies.records.thought_record');
            Volt::route('therapies/in-progress/records/emotion-record', 'doctor.therapy.records.emotion_record')->name('therapies.records.emotion_record');
            Volt::route('therapies/in-progress/records/committed-action', 'doctor.therapy.records.committed_action')->name('therapies.records.committed_action');

        });
    });
});

require __DIR__.'/auth.php';
