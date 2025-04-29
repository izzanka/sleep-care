<?php

namespace App\Service;

use App\Enum\TherapyStatus;
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

        if ($status) {
            $query->where('status', $status);
        }

        $query->latest();

        return $status === TherapyStatus::COMPLETED->value ? $query->get() : $query->first();
    }
}
