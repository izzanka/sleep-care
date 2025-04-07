<?php

namespace App\Enum;

enum RecordType: string
{
    case SLEEP_DIARY = 'sleep_diary';
    case IDENTIFY_VALUE = 'identify_value';
    case THOUGHT_RECORD = 'thought_record';
    case EMOTION_RECORD = 'emotion_record';
    case COMMITTED_ACTION = 'committed_action';

    public function label(): string
    {
        return match ($this) {
            self::SLEEP_DIARY => 'Catatan Tidur',
            self::IDENTIFY_VALUE => 'Identifikasi Nilai',
            self::THOUGHT_RECORD => 'Catatan Pikiran',
            self::EMOTION_RECORD => 'Catatan Emosi',
            self::COMMITTED_ACTION => 'Tindakan Berkomitmen',
        };
    }
}
