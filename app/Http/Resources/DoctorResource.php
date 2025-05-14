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
            'graduate' => $this->graduate,
            'phone' => $this->phone,
            'name_title' => $this->name_title,
            'total_rating' => $this->usersRated() ?? 0,
            'avg_rating' => $this->averageRating() ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->user),
        ];
    }
}
