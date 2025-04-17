<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DoctorController extends Controller
{
    public function getAll()
    {
        $doctors = Doctor::with('user')->latest()->get();
        return Response::success([
            'doctors' => $doctors,
        ], 'Get all doctor success.');
    }
}
