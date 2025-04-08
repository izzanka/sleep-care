<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentifyValue extends Model
{
    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function questionAnswers()
    {
        return $this->hasMany(IdentifyValueQuestionAnswer::class)->orderBy('created_at');
    }
}
