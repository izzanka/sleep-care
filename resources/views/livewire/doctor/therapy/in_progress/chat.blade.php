<?php

use App\Enum\TherapyStatus;
use App\Models\Chat;
use App\Models\Therapy;
use App\Models\User;
use Livewire\Volt\Component;

new class extends Component {
    public bool $isOnline = false;
    public ?int $patientID = null;
    public ?int $therapyID = null;
    public ?string $message = '';

    public function mount()
    {
        $this->dispatch('scroll-to-bottom');
    }

    public function with()
    {
        $therapy = $this->getOngoingTherapy();
        $chats = Chat::where('receiver_id', $therapy->patient->id)->orWhere('sender_id', $therapy->patient->id)->orderBy('created_at')->get();

        if ($therapy && $therapy->patient) {
            $this->therapyID = $therapy->id;
            $this->patientID = $therapy->patient->id;
        }

        return [
            'therapy' => $therapy,
            'chats' => $chats,
        ];
    }

    public function send()
    {
        $validated = $this->validate([
           'message' => ['required','string']
        ]);

        Chat::create([
            'therapy_id' => $this->therapyID,
            'sender_id' => auth()->id(),
            'receiver_id' => $this->patientID,
            'message' => $validated['message'],
            'created_at' => now(),
        ]);

        $this->reset(['message']);
        $this->dispatch('scroll-to-bottom');
    }

    public function checkPatientOnlineStatus()
    {
        $this->isOnline = User::where('id', $this->patientID)->value('is_online') ?? false;
    }

    protected function getOngoingTherapy()
    {
        return Therapy::with('patient')
            ->where('status', TherapyStatus::IN_PROGRESS->value)
            ->first();
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Percakapan dengan pasien'])
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
                            <span class="text-xs text-gray-200 block text-right mt-1">{{$chat->created_at->format('H:i')}}</span>
                        </div>
                    </div>
                @else
                    <div class="flex items-start space-x-2 mt-2">
                        <div class="bg-gray-200 p-3 rounded-lg max-w-xs">
                            <p class="text-sm text-black">{{$chat->message}}</p>
                            <span class="text-xs text-gray-500 block text-right mt-1">{{$chat->created_at->format('H:i')}}</span>
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
