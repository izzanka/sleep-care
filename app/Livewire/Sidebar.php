<?php

namespace App\Livewire;

use App\Enum\TherapyStatus;
use App\Enum\UserRole;
use App\Models\Question;
use App\Models\Therapy;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Sidebar extends Component
{
    public $unreadNotificationsCount;

    public $hasOngoingTherapy;

    public $completedTherapiesCount;

    public $allTherapyCount;

    public $doctorCount;

    public $patientCount;

    public $generalSettingCount;

    public $questionCount;

    public $user;

    public $unreadChatsCount;

    public $unreadThoughtRecord;

    public $unreadSleepDiary;

    public $unreadCommittedAction;

    public $unreadEmotionRecord;

    public $unreadIdentifyValue;

    public function mount()
    {
        $this->user = Auth::user();

        if ($this->user) {
            $this->unreadNotificationsCount = $this->user->unreadNotifications()->count();

            if (Gate::allows('isDoctor', $this->user)) {
                $doctor = $this->user->doctor;
                $ongoingTherapy = $doctor->therapies()
                    ->where('status', TherapyStatus::IN_PROGRESS->value)
                    ->first();
                $this->hasOngoingTherapy = $ongoingTherapy !== null;
                if ($this->hasOngoingTherapy) {
                    $thoughtRecord = $ongoingTherapy->thoughtRecords->first();
                    $committedAction = $ongoingTherapy->committedActions->first();
                    $emotionRecord = $ongoingTherapy->emotionRecords->first();
                    $identifyValue = $ongoingTherapy->identifyValues->first();

                    $this->unreadChatsCount = $doctor->user->received()->where('therapy_id', $ongoingTherapy->id)->whereNull('read_at')->count();
                    $this->unreadThoughtRecord = $thoughtRecord?->questionAnswers()?->whereNull('is_read')->exists();
                    $this->unreadCommittedAction = $committedAction?->questionAnswers()?->whereNull('is_read')->exists();
                    $this->unreadEmotionRecord = $emotionRecord?->questionAnswers()?->whereNull('is_read')->exists();
                    $this->unreadIdentifyValue = $identifyValue?->questionAnswers()?->whereNull('is_read')->exists();
                    $this->unreadSleepDiary = $ongoingTherapy?->sleepDiaries()?->whereHas('questionAnswers', function ($query) {
                        $query->whereNull('is_read');
                    })->exists();
                }
                $this->completedTherapiesCount = $doctor->therapies->where('status', TherapyStatus::COMPLETED->value)->count();
            }

            if (Gate::allows('isAdmin', $this->user)) {
                $roleCounts = User::selectRaw('role, COUNT(*) as count')
                    ->whereIn('role', [UserRole::DOCTOR->value, UserRole::PATIENT->value])
                    ->groupBy('role')
                    ->pluck('count', 'role');

                $this->allTherapyCount = Therapy::count();
                $this->doctorCount = $roleCounts[UserRole::DOCTOR->value] ?? 0;
                $this->patientCount = $roleCounts[UserRole::PATIENT->value] ?? 0;
                $this->generalSettingCount = count(Schema::getColumnListing('generals')) - 3;
                $this->questionCount = Question::count();
            }
        }
    }

    public function render()
    {
        return view('livewire.sidebar');
    }
}
