<?php

namespace App\Enum;

enum Problem: string
{
    case STRES = 'stres';
    case ADIKSI = 'adiksi';
    case DEPRESI = 'depresi';
    case TRAUMA = 'trauma';

    public function label(): string
    {
        return match ($this) {
            self::STRES => 'Stres',
            self::ADIKSI => 'Adiksi',
            self::DEPRESI => 'Depresi',
            self::TRAUMA => 'Trauma',
        };
    }
}
