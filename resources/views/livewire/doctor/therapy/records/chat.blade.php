<?php

use App\Enum\TherapyStatus;
use App\Service\ChatService;
use App\Service\TherapyService;
use App\Service\UserService;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;
    protected ChatService $chatService;
    protected UserService $userService;

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

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if(!$this->therapy){
            return $this->redirectRoute('doctor.therapies.in_progress.index');
        }
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
            session()->flash('status', ['message' => 'Gagal mengirimkan pesan.', 'success' => false]);
        }
        $this->reset(['message']);
        $this->dispatch('scroll-to-bottom');
    }

    public function with()
    {
        $this->chatService->markAsRead($this->therapy->id, $this->therapy->doctor->user->id);
        $chats = $this->chatService->get($this->therapy->id);
        $this->dispatch('scroll-to-bottom');

        return [
            'chats' => $chats,
        ];
    }
}; ?>

<section>
    @if($therapy->status === TherapyStatus::IN_PROGRESS)
        <flux:callout icon="information-circle" class="mb-4" color="blue"
                      x-data="{ visible: localStorage.getItem('hideMessageChat') !== 'true' }"
                      x-show="visible"
                      x-init="$watch('visible', value => !value && localStorage.setItem('hideMessageChat', 'true'))">
            <flux:callout.heading>Percakapan</flux:callout.heading>

            <flux:callout.text>
                Fitur ini digunakan untuk berkomunikasi antara anda dengan pasien secara langsung, termasuk untuk mendiskusikan perkembangan terapi, menjadwalkan sesi terapi, atau memberikan dukungan tambahan.
                <br><br>
                <flux:callout.link href="#" @click="visible = false">Jangan tampilkan lagi.</flux:callout.link>
            </flux:callout.text>
        </flux:callout>

        <div class="h-[400px] flex flex-col rounded-lg overflow-hidden border dark:border-transparent">
            <!-- Chat Messages -->
            <div class="flex-1 p-4 overflow-y-auto custom-scrollbar" id="chat-container">
                @foreach($chats as $chat)
                    @if($chat->sender_id == auth()->id())
                        <div class="flex items-start space-x-2 justify-end mt-2 text-white">
                            <div class="bg-blue-400 p-3 rounded-lg max-w-xs">
                                <p class="text-sm break-words">{{ $chat->message }}</p>
                                <span class="text-xs block text-right mt-1">
                            {{ $chat->created_at->format('H:i') }}
                                    {{ $chat->read_at ? ' (Dibaca)' : '' }}
                        </span>
                            </div>
                        </div>
                    @else
                        <div class="flex items-start space-x-2 mt-2 text-white">
                            <div class="bg-zinc-400 p-3 rounded-lg max-w-xs">
                                <p class="text-sm break-words">{{ $chat->message }}</p>
                                <span class="text-xs block text-right mt-1">{{ $chat->created_at->format('H:i') }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Chat Input -->
            <div class="p-3 flex items-center gap-2 bg-white dark:bg-zinc-700 border-t dark:border-transparent">
                <input
                    type="text"
                    class="flex-1 p-2 border rounded-lg text-sm outline-none focus:ring"
                    placeholder="Tulis sebuah pesan..."
                    wire:model="message"
                    wire:keydown.enter="send"
                >
            </div>
        </div>
    @else
        @foreach($chats as $chat)
        <div class="flex mt-2 mb-2">
            <flux:heading class="w-20">
                {{ $chat->sender_id === auth()->id() ? 'Anda' : 'Pasien' }}:
            </flux:heading>
            <flux:text class="flex-1">
                {{$chat->message}}
            </flux:text>
        </div>
        @endforeach
    @endif
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
