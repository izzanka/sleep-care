<?php

namespace App\Service;

use App\Models\User;

class UserService
{
    public function get(?string $email = null, ?int $role = null, ?bool $verified = null, ?bool $is_active = null, ?int $id = null)
    {
        $query = User::query();

        if ($email) {
            $query->where('email', $email);
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($is_active) {
            $query->where('is_active', true);
        }

        if (! is_null($verified)) {
            if ($verified) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
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
