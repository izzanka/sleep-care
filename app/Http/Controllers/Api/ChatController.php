<?php

namespace App\Http\Controllers\Api;

use App\Enum\TherapyStatus;
use App\Http\Controllers\Controller;
use App\Service\ChatService;
use App\Service\TherapyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery\Exception;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chatService,
        protected TherapyService $therapyService) {}

    public function get()
    {
        try {

            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $chats = $this->chatService->get($therapy->id);
            if (! $chats) {
                return Response::error('Gagal mendapatkan pesan.', 500);
            }

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

            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
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

            return Response::success($chat, 'Berhasil mengirimkan pesan.');

        } catch (\Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }

    public function update(int $id)
    {
        try {

            $therapy = $this->therapyService->get(patientId: auth()->id(), status: TherapyStatus::IN_PROGRESS->value)->first();
            if (! $therapy) {
                return Response::error('Terapi tidak ditemukan.', 404);
            }

            $chat = $this->chatService->get(id: $id, receiver_id: auth()->id())->first();
            if (! $chat) {
                return Response::error('Pesan tidak ditemukan.', 404);
            }

            if($chat->read_at != null){
                return Response::success('Pesan sudah dibaca.', 500);
            }

            $this->chatService->markAsRead($therapy->id, auth()->id());

            return Response::success(null, 'Pesan berhasil dibaca.');

        } catch (Exception $exception) {
            return Response::error($exception->getMessage(), 500);
        }
    }
}
