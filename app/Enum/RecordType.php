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
            self::SLEEP_DIARY => 'Sleep Diary',
            self::IDENTIFY_VALUE => 'Identify Value',
            self::THOUGHT_RECORD => 'Thought Record',
            self::EMOTION_RECORD => 'Emotion Record',
            self::COMMITTED_ACTION => 'Committed Action',
        };
    }
}
