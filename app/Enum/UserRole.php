<?php

namespace App\Enum;

enum UserRole: int
{
    case ADMIN = 1;
    case DOCTOR = 2;
    case PATIENT = 3;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::DOCTOR => 'Psikolog',
            self::PATIENT => 'Pasien',
        };
    }
}
