<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommittedAction extends Model
{
    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(CommittedActionQuestionAnswer::class)->orderBy('created_at');
    }
}
