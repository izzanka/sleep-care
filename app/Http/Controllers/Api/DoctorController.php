<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorResource;
use App\Service\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    public function __construct(protected DoctorService $doctorService) {}

    public function getAll(Request $request)
    {
        $allowedColumns = ['registered_year', 'created_at', 'is_therapy_in_progress'];
        $allowedSorts = ['asc', 'desc'];

        $validated = $request->validate([
            'order_by' => ['required', Rule::in($allowedColumns)],
            'sort' => ['required', Rule::in($allowedSorts)],
            'paginate' => ['required', 'integer'],
        ]);

        try {

            $doctors = $this->doctorService->get($validated['order_by'], $validated['sort'], $validated['paginate']);

            return Response::success([
                'doctors' => DoctorResource::collection($doctors),
            ], 'Berhasil mengambil data psikolog.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function getById(int $id)
    {
        try {

            $doctor = $this->doctorService->get(id: $id)->first();
            if (! $doctor) {
                return Response::error('Psikolog tidak ditemukan.', 404);
            }

            return Response::success(new DoctorResource($doctor), 'Berhasil mengambil data detail psikolog.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
