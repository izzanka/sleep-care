<?php

namespace App\Service;

use App\Enum\ModelFilter;
use App\Models\User;

class UserService
{
    public function get(?array $filters = null)
    {
        $query = User::query();

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
