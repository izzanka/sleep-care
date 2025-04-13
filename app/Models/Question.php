<?php

namespace App\Models;

use App\Enum\QuestionType;
use App\Enum\RecordType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class Question extends Model
{
    use Searchable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'record_type' => RecordType::class,
            'type' => QuestionType::class,
        ];
    }

    #[SearchUsingFullText(['question'])]
    public function toSearchableArray()
    {
        return [
            'question' => $this->question,
        ];
    }
}
