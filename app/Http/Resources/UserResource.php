<?php

namespace App\Http\Resources;

use App\Enum\TherapyStatus;
use App\Enum\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isPatient = $this->role === UserRole::PATIENT->value;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'age' => $this->age,
            'gender' => $this->gender,
            'is_active' => (bool) $this->is_active,
            'is_online' => (bool) $this->is_online,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'avatar' => ! $isPatient && $this->avatar ? asset('storage/'.$this->avatar) : null,
            'is_therapy_in_progress' => $isPatient ? $this->therapies()->where('status', TherapyStatus::IN_PROGRESS->value)->exists() : null,
            'problems' => $isPatient && $this->problems ? json_decode($this->problems) : null,
        ];
    }
}
