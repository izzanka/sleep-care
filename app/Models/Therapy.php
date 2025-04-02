<?php

namespace App\Models;

use App\Enum\TherapyStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Therapy extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => TherapyStatus::class,
        ];
    }

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

    protected function startDate(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value)->format('d F Y')
        );
    }

    protected function endDate(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => Carbon::parse($value)->format('d F Y')
        );
    }
}
