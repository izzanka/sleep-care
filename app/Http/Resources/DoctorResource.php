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
            'graduated_from' => $this->graduated_from,
            'phone' => $this->phone,
            'about' => $this->about,
            'is_available' => (bool) $this->is_available,
            'total_rating' => $this->timesRated() ?? 0,
            'avg_rating' => number_format($this->averageRating, 1) ?? '0',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->user),
        ];
    }
}
