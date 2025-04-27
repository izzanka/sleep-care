<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThoughtRecord extends Model
{
    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(ThoughtRecordQuestionAnswer::class);
    }
}
