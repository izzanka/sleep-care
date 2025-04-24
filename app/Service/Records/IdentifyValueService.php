<?php

namespace App\Service\Records;

use App\Enum\ModelFilter;
use App\Models\EmotionRecord;
use App\Models\IdentifyValue;

class IdentifyValueService
{
    public function get(?array $filters = null)
    {
        $query = IdentifyValue::query();

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
