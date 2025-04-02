<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function isAdmin(User $user)
    {
        return $user->role == UserRole::ADMIN->value;
    }

    public function isDoctor(User $user)
    {
        return $user->role == UserRole::DOCTOR->value;
    }

    public function isPatient(User $user)
    {
        return $user->role == UserRole::PATIENT->value;
    }
}
