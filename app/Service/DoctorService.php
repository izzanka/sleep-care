<?php

namespace App\Service;

use App\Enum\ModelFilter;
use App\Models\Doctor;

class DoctorService
{
    public function get(?array $filters = null)
    {
        $query = Doctor::with('user');

        if ($filters) {
            foreach ($filters as $filter) {
                switch ($filter['operation']) {
                    case ModelFilter::EQUAL->name:
                        $query->where($filter['column'], $filter['value']);
                        break;

                    case ModelFilter::ORDER_BY->name:
                        $query->orderBy($filter['column'], $filter['value']);
                        break;
                }
            }
        }

        return $query->get();
    }
}
