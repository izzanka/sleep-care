<?php

namespace App\Service;

use App\Models\User;

class UserService
{
    public function get(?string $email = null, ?int $role = null, ?bool $verified = null, ?int $id = null)
    {
        $query = User::query();

        if ($email) {
            $query->where('email', $email);
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($verified) {
            $query->whereNotNull('email_verified_at');
        }

        if ($id) {
            $query->where('id', $id);
        }

        return $query->get();
    }

    public function getPatientOnlineStatus(int $patientId)
    {
        return User::find($patientId)->value('is_online');
    }
}
