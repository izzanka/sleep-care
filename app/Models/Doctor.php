<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use willvincent\Rateable\Rateable;

class Doctor extends Model
{
    use HasFactory, Rateable;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function therapies()
    {
        return $this->hasMany(Therapy::class);
    }

    public function rooms()
    {
        return $this->hasMany(ChatRoom::class);
    }
}
