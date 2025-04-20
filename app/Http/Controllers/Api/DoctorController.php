<?php

namespace App\Http\Controllers\Api;

use App\Enum\ModelFilter;
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
        $allowedColumns = ['registered_year', 'created_at'];
        $allowedSorts = ['asc', 'desc'];

        $validated = $request->validate([
            'order_by' => ['required', Rule::in($allowedColumns)],
            'sort' => ['required', Rule::in($allowedSorts)],
            'user_id' => ['nullable', 'numeric'],
        ]);

        try {

            $filters = [
                [
                    'operation' => ModelFilter::ORDER_BY->name,
                    'column' => $validated['order_by'],
                    'value' => $validated['sort'],
                ],
            ];

            if (isset($validated['user_id'])) {
                $filters[] = [
                    'operation' => ModelFilter::EQUAL->name,
                    'column' => 'user_id',
                    'value' => $validated['user_id'],
                ];
            }

            $doctors = $this->doctorService->get($filters);

            if ($doctors->isEmpty()) {
                return Response::error('Doctor not found', 404);
            }

            return Response::success([
                'doctors' => DoctorResource::collection($doctors),
            ], 'Get doctor success.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
