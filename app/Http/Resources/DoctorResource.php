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
            'user' => new UserResource($this->user),
        ];
    }
}
