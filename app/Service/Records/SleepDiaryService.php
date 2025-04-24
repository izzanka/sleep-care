<?php

namespace App\Service\Records;

use App\Enum\ModelFilter;
use App\Models\IdentifyValue;
use App\Models\SleepDiary;

class SleepDiaryService
{
    public function get(?array $filters = null)
    {
        $query = SleepDiary::query();

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
