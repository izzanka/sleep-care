<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmotionRecord extends Model
{
    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(EmotionRecordQuestionAnswer::class)->orderBy('created_at');
    }
}
