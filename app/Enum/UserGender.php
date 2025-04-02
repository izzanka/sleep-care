<?php

namespace App\Enum;

enum UserGender: string
{
    case PRIA = 'pria';
    case PEREMPUAN = 'perempuan';

    public function label(): string
    {
        return match ($this) {
            self::PRIA => 'Pria',
            self::PEREMPUAN => 'Perempuan',
        };
    }
}
