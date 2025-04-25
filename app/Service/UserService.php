<?php

namespace App\Service;

use App\Enum\UserRole;
use App\Models\User;

class UserService
{
    public function getUnverifiedPatient(string $email)
    {
        return User::where('email', $email)->where('role', UserRole::PATIENT->value)
            ->whereNull('email_verified_at')->latest()->first();
    }

    public function getPatient(string $email)
    {
        return User::where('email', $email)->where('role', UserRole::PATIENT->value)->first();
    }

    public function getAdmin()
    {
        return User::where('role', UserRole::ADMIN->value)->first();
    }

    public function getPatientOnlineStatus(int $patientId)
    {
        return User::where('id', $patientId)->value('is_online') ?? false;
    }
}
