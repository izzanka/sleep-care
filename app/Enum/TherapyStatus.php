<?php

namespace App\Enum;

enum TherapyStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'Berlangsung',
            self::COMPLETED => 'Selesai',
        };
    }
}
