<?php

namespace App\Enum;

enum QuestionType: string
{
    case TEXT = 'text';
    case BINARY = 'binary';

    case DATE = 'date';
    case DATE_TIME = 'date_time';
    case TIME = 'time';
    case NUMBER = 'number';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::BINARY => 'Binary',
            self::DATE => 'Date',
            self::DATE_TIME => 'Date Time',
            self::TIME => 'Time',
            self::NUMBER => 'Number',
        };
    }
}
