<?php

namespace App\Service;

use App\Models\Answer;

class AnswerService
{
    public function get(int $id)
    {
        return Answer::find($id);
    }
}
