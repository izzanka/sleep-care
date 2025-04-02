<?php

namespace App\Enum;

enum QuestionType: string
{
    case OPEN = 'open';
    case BINARY = 'binary';
    case SCALE = 'scale';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Open',
            self::BINARY => 'Binary',
            self::SCALE => 'Scale',
        };
    }
}
