<?php

namespace App\Service;

use App\Models\Doctor;

class DoctorService
{
    public function get(string $orderBy = 'created_at', string $sort = 'desc', int $paginate = 15, ?int $id = null)
    {
        $query = Doctor::query();

        if ($id) {
            $query->where('id', $id);
        }

        return $query->orderBy($orderBy, $sort)->paginate($paginate);
    }
}
