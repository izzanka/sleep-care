<?php

namespace App\Livewire\Records;

use App\Service\ChatService;
use App\Service\TherapyService;
use Livewire\Component;

class Chat extends Component
{
    protected TherapyService $therapyService;

    protected ChatService $chatService;

    public $therapy;

    public $chats;

    public function boot(TherapyService $therapyService, ChatService $chatService)
    {
        $this->therapyService = $therapyService;
        $this->chatService = $chatService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if (! $this->therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);
            return $this->redirectRoute('doctor.therapies.completed.index');
        }
        $this->chats = $this->chatService->get($therapyId);
    }

    public function render()
    {
        return view('livewire.records.chat');
    }
}
