<?php

namespace App\Http\Resources;

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
        $isPatient = $this->role == UserRole::PATIENT->value;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'age' => $this->age,
            'gender' => $this->gender,
            'is_active' => (bool) $this->is_active,
            'is_online' => (bool) $this->is_online,
            'is_therapy_in_progress' => (bool) $this->is_therapy_in_progress,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'avatar' => $isPatient ? null : ($this->avatar ? asset('storage/'.$this->avatar) : asset('storage/img/avatars/doctor.png')),
            'problems' => $isPatient && $this->problems ? json_decode($this->problems) : null,
        ];
    }
}
