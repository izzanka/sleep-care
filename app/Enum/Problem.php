<?php

namespace App\Enum;

enum Problem: string
{
    case STRES = 'stres';
    case GANGGUAN_KECEMASAN = 'gangguan_kecemasan';
    case ADIKSI = 'adiksi';
    case DEPRESI = 'depresi';
    case TRAUMA = 'trauma';
    case GANGGUAN_KEPRIBADIAN = 'gangguan_kepribadian';
    case GANGGUAN_MOOD = 'gangguan_mood';

    public function label(): string
    {
        return match ($this) {
            self::STRES => 'Stres',
            self::GANGGUAN_KECEMASAN => 'Gangguan Kecemasan',
            self::ADIKSI => 'Adiksi',
            self::DEPRESI => 'Depresi',
            self::TRAUMA => 'Trauma',
            self::GANGGUAN_KEPRIBADIAN => 'Gangguan Kepribadian',
            self::GANGGUAN_MOOD => 'Gangguan Mood',
        };
    }
}
