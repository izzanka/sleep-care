<?php

namespace App\Http\Controllers\Api;

use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\ChatService;
use App\Service\TherapyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService,
        protected TherapyService $therapyService) {}

    public function get()
    {
        try {

            $therapy = $this->therapyService->find(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value);
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $chats = $this->chatService->get($therapy->id);

            return Response::success([
                'chats' => $chats,
            ], 'Berhasil mendapatkan data percakapan.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        try {

            $therapy = $this->therapyService->find(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value);
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $validated['therapy_id'] = $therapy->id;
            $validated['sender_id'] = auth()->id();
            $validated['receiver_id'] = $therapy->doctor_id;

            $chat = $this->chatService->store($validated);
            if (! $chat) {
                return Response::error('Gagal mengirimkan pesan.', 500);
            }

            return Response::success([
                'chat' => $chat,
            ], 'Berhasil mengirimkan pesan.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
