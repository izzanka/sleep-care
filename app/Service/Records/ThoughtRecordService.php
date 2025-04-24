<?php

namespace App\Service\Records;

use App\Enum\ModelFilter;
use App\Models\IdentifyValue;
use App\Models\ThoughtRecord;

class ThoughtRecordService
{
    public function get(?array $filters = null)
    {
        $query = ThoughtRecord::query();

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
