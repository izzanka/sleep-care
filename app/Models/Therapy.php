<?php

namespace App\Models;

use App\Enum\TherapyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Therapy extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => TherapyStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function schedules()
    {
        return $this->hasMany(TherapySchedule::class);
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function chat()
    {
        return $this->hasOne(Chat::class);
    }

    public function sleepDiaries()
    {
        return $this->hasMany(SleepDiary::class);
    }

    public function identifyValues()
    {
        return $this->hasMany(IdentifyValue::class);
    }

    public function emotionRecords()
    {
        return $this->hasMany(EmotionRecord::class);
    }

    public function thoughtRecords()
    {
        return $this->hasMany(ThoughtRecord::class);
    }

    public function committedActions()
    {
        return $this->hasMany(CommittedAction::class);
    }
}
