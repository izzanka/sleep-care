<?php

namespace App\Service;

use App\Models\Question;

class QuestionService
{
    public function get(?string $record_type = null, ?int $id = null)
    {
        $query = Question::query();

        if ($record_type) {
            $query->where('record_type', $record_type);
        }

        if ($id) {
            $query->where('id', $id);
        }

        return $query->get();
    }
}
