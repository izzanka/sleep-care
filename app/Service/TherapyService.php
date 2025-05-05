<?php

namespace App\Service;

use App\Models\Therapy;

class TherapyService
{
    public function find(?int $doctorId = null, ?int $patientId = null, ?string $status = null, ?int $id = null)
    {
        $query = Therapy::query();

        if ($id) {
            $query->where('id', $id);
        }

        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        if ($patientId) {
            $query->where('patient_id', $patientId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->get();
    }
}
