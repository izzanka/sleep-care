<?php

namespace App\Service;

use App\Models\Question;

class QuestionService
{
    public function get(string $record_type)
    {
        return Question::where('record_type', $record_type)->get();
    }
}
