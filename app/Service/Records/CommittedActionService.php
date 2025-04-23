<?php

namespace App\Service\Records;

use App\Enum\ModelFilter;
use App\Models\CommittedAction;
use App\Models\Therapy;

class CommittedActionService
{
    public function get(?array $filters = null)
    {
        $query = CommittedAction::query();

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
