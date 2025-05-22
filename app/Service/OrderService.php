<?php

namespace App\Service;

use App\Models\Order;

class OrderService
{
    public function get(?int $therapyId = null, ?int $patientId = null, ?string $id = null)
    {
        $query = Order::query();

        if ($therapyId) {
            $query->where('therapy_id', $therapyId);
        }

        if ($patientId) {
            $query->whereHas('therapy', function ($query) use ($patientId) {
                $query->where('patient_id', $patientId);
            });
        }

        if ($id) {
            $query->where('id', $id);
        }

        return $query->latest()->get();
    }
}
