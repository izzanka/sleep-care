<?php

use App\Enum\TherapyStatus;
use App\Service\ChatService;
use App\Service\TherapyService;
use App\Service\UserService;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;
    protected ChatService $chatService;
    protected UserService $userService;

    public bool $isOnline = false;
    public ?string $message = '';

    public $therapy;

    public function boot(TherapyService $therapyService,
                         ChatService    $chatService,
                         UserService    $userService)
    {
        $this->therapyService = $therapyService;
        $this->chatService = $chatService;
        $this->userService = $userService;
    }

    public function mount()
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->find(doctorId: $doctorId, status: TherapyStatus::IN_PROGRESS->value);
        $this->isOnline = $this->userService->getPatientOnlineStatus($this->therapy->patient_id);
    }

    public function send()
    {
        $validated = $this->validate([
            'message' => ['required', 'string']
        ]);

        $validated['therapy_id'] = $this->therapy->id;
        $validated['sender_id'] = auth()->id();
        $validated['receiver_id'] = $this->therapy->patient_id;

        $result = $this->chatService->store($validated);
        if (!$result) {
            Session::flash('status', ['message' => 'Gagal mengirimkan pesan.', 'success' => false]);
        }
        $this->reset(['message']);
        $this->dispatch('scroll-to-bottom');
    }

    public function checkPatientOnlineStatus()
    {
        $this->isOnline = $this->userService->getPatientOnlineStatus($this->therapy->patient_id);
    }

    public function with()
    {
        $chats = $this->chatService->get($this->therapy->id);
        $this->dispatch('scroll-to-bottom');

        return [
            'chats' => $chats,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Percakapan'])
    <div class="h-[440px] rounded-lg flex flex-col">
        <div class="dark:bg-zinc-700 p-4 flex items-center gap-3 rounded-t-lg bg-white border dark:border-transparent">
            <div class="flex items-center gap-2" wire:poll.5s.visible="checkPatientOnlineStatus">
                @if($isOnline)
                    <flux:avatar badge badge:circle badge:color="green" name="{{$therapy->patient->name}}"/>
                @else
                    <flux:avatar badge badge:circle badge:color="zinc" name="{{$therapy->patient->name}}"/>
                @endif
                <span>{{$therapy->patient->name}}</span>
            </div>
        </div>

        <div class="flex-1 p-4 overflow-y-auto custom-scrollbar border dark:border-transparent" id="chat-container">
            @foreach($chats as $chat)
                @if($chat->sender_id == auth()->id())
                    <div class="flex items-start space-x-2 justify-end mt-2">
                        <div class="bg-green-500 text-white p-3 rounded-lg max-w-xs">
                            <p class="text-sm break-words">{{$chat->message}}</p>
                            <span
                                class="text-xs text-gray-200 block text-right mt-1">{{$chat->created_at->format('H:i')}}</span>
                        </div>
                    </div>
                @else
                    <div class="flex items-start space-x-2 mt-2">
                        <div class="bg-gray-200 p-3 rounded-lg max-w-xs">
                            <p class="text-sm text-black">{{$chat->message}}</p>
                            <span
                                class="text-xs text-gray-500 block text-right mt-1">{{$chat->created_at->format('H:i')}}</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="p-3 flex items-center gap-2 bg-white border dark:bg-zinc-700 dark:border-transparent rounded-b-lg">
            <input type="text"
                   class="flex-1 p-2 border rounded-lg text-sm outline-none focus:ring"
                   placeholder="Tulis sebuah pesan..." wire:model="message" wire:keydown.enter="send">
        </div>
    </div>
</section>

@script
<script>
    $wire.on('scroll-to-bottom', () => {
        setTimeout(() => {
            const container = document.getElementById('chat-container');
            if (!container) return;
            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth'
            });
        }, 100);
    });
</script>
@endscript
