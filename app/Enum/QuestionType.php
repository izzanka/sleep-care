<?php

namespace App\Enum;

enum QuestionType: string
{
    case TEXT = 'text';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case TIME = 'time';
    case NUMBER = 'number';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Teks',
            self::BOOLEAN => 'Boolean',
            self::DATE => 'Tanggal',
            self::TIME => 'Waktu',
            self::NUMBER => 'Angka',
        };
    }
}
