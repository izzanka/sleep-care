<?php

namespace App\Service;

use App\Models\Doctor;

class DoctorService
{
    public function get(string $orderBy, string $sort, int $paginate = 15)
    {
        return Doctor::whereHas('user', function ($query) {
            $query->where('is_active', true);
        })->orderBy($orderBy, $sort)->paginate($paginate);
    }

    public function find(int $id)
    {
        return Doctor::whereHas('user', function ($query) {
            $query->where('is_active', true);
        })->find($id);
    }
}
