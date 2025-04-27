<?php

namespace App\Http\Controllers\Api;

use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\TherapyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;

class TherapyController extends Controller
{
    public function __construct(protected TherapyService $therapyService) {}

    public function get(Request $request)
    {
        $validated = $request->validate([
            'status' => ['required', new Enum(TherapyStatus::class)],
        ]);

        try {

            $therapy = $this->therapyService->find(patientId: auth()->id(), status: $validated['status']);
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            return Response::success([
                'therapy' => $therapy,
            ], 'Berhasil mendapatkan data terapi.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
