<?php

namespace App\Service;

use App\Enum\ModelFilter;
use App\Enum\TherapyStatus;
use App\Models\Therapy;
use Illuminate\Support\Facades\DB;

class TherapyService
{
    public function get(?array $filters = null)
    {
        $query = Therapy::query();

        if ($filters) {
            foreach ($filters as $filter) {
                switch ($filter['operation']) {
                    case ModelFilter::EQUAL->name:
                        $query->where($filter['column'], $filter['value']);
                        break;
                }
            }
        }

        return $query->get();
    }

    public function getCurrentTherapy(int $doctorId)
    {
        $filters = [
            [
                'operation' => ModelFilter::EQUAL,
                'column' => 'doctor_id',
                'value' => $doctorId,
            ],
            [
                'operation' => ModelFilter::EQUAL,
                'column' => 'status',
                'value' => TherapyStatus::IN_PROGRESS->value,
            ],
        ];

        return $this->get($filters)[0] ?? null;
    }
}
