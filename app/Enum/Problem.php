<?php

namespace App\Enum;

enum Problem: string
{
    case STRES = 'stres';
    case ADIKSI = 'adiksi';
    case DEPRESI = 'depresi';
    case TRAUMA = 'trauma';
    case GANGGUAN_KEPRIBADIAN = 'gangguan_kepribadian';
    case GANGGUAN_MOOD = 'gangguan_mood';
    case GANGGUAN_KECEMASAN = 'gangguan_kecemasan';

    public function label(): string
    {
        return match ($this) {
            self::STRES => 'Stres',
            self::ADIKSI => 'Adiksi',
            self::DEPRESI => 'Depresi',
            self::TRAUMA => 'Trauma',
            self::GANGGUAN_KEPRIBADIAN => 'Gangguan Kepribadian',
            self::GANGGUAN_MOOD => 'Gangguan Mood',
            self::GANGGUAN_KECEMASAN => 'Gangguan Kecemasan',
        };
    }
}
