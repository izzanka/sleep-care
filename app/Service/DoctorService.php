<?php

namespace App\Service;

use App\Models\Doctor;

class DoctorService
{
    public function get(string $orderBy = 'created_at', string $sort = 'desc', int $paginate = 15, ?int $id = null)
    {
        $query = Doctor::query()->select('doctors.*')->with('user');

        if ($id) {
            $query->where('id', $id);
        }

        if ($orderBy === 'is_therapy_in_progress') {
            $query->join('users', 'users.id', '=', 'doctors.user_id')
                ->orderBy('users.is_therapy_in_progress', $sort);
        } else {
            $query->orderBy("doctors.$orderBy", $sort);
        }

        $query->where('is_available', true);

        return $query->paginate($paginate);
    }
}
