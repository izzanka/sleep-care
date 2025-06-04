<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TherapyResource extends JsonResource
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
            'doctor_id' => $this->doctor_id,
            'patient_id' => $this->patient_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'doctor_fee' => $this->doctor_fee,
            'application_fee' => $this->application_fee,
            'comment' => $this->comment,
            'rating' => $this->doctor->ratings()->where('user_id', $this->patient_id)->value('rating') ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
