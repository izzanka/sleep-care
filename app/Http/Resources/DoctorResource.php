<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'registered_year' => $this->registered_year,
            'phone' => $this->phone,
            'name_title' => $this->name_title,
            'created_at' => $this->created_at,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'avatar' => $this->user->avatar,
                'age' => $this->user->age,
                'gender' => $this->user->gender,
                'is_active' => $this->user->is_active,
                'is_online' => $this->user->is_online,
                'created_at' => $this->user->created_at,
                'deleted_at' => $this->user->deleted_at,
            ],
        ];
    }
}
