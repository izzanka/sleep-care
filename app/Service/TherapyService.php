<?php

namespace App\Service;

use App\Enum\ModelFilter;
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
}
