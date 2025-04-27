<?php

namespace App\Service;

use App\Models\Therapy;

class TherapyService
{
    public function find(?int $doctorId = null, ?int $patientId = null, ?string $status = null)
    {
        $query = Therapy::query();

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        return $query->where('status', $status)->latest()->first();
    }
}
