<?php

use App\Enum\TherapyStatus;
use App\Service\TherapyService;
use Livewire\Volt\Component;

new class extends Component {
    public $id;
    public $history = 'index';

    protected TherapyService $therapyService;

    public function boot(TherapyService $therapyService)
    {
        $this->therapyService = $therapyService;
    }

    public function mount(int $id)
    {
        $doctorId = auth()->user()->doctor->id;
        $therapy = $this->therapyService->get(doctorId: $doctorId, status: TherapyStatus::COMPLETED->value, id: $id)->first();
        if (!$therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);
            return $this->redirectRoute('doctor.therapies.completed.index');
        }
    }

    public function setHistory($value)
    {
        $this->history = $value;
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Detail Riwayat'])

    <div class="flex flex-wrap gap-4 items-start mb-6">
        <flux:radio.group class="mb-0" variant="segmented" label="Catatan:" wire:model="history">
            <flux:radio
                label="Tidur"
                value="sleep_diary"
                wire:click="setHistory('sleep_diary')"
            />
            <flux:radio
                label="Nilai"
                value="identify_value"
                wire:click="setHistory('identify_value')"
            />
            <flux:radio
                label="Pikiran"
                value="thought_record"
                wire:click="setHistory('thought_record')"
            />
            <flux:radio
                label="Emosi"
                value="emotion_record"
                wire:click="setHistory('emotion_record')"
            />
            <flux:radio
                label="Tindakan"
                value="committed_action"
                wire:click="setHistory('committed_action')"
            />
        </flux:radio.group>

        <flux:radio.group class="mb-0" variant="segmented" label="Lainnya:" wire:model="history">
            <flux:radio
                label="Jadwal"
                value="schedule"
                wire:click="setHistory('schedule')"
            />
            <flux:radio
                label="Percakapan"
                value="chat"
                wire:click="setHistory('chat')"
            />
        </flux:radio.group>
    </div>

    <flux:separator variant="subtle" class="mb-6"/>

    <div wire:loading wire:target="setHistory">
        <flux:icon.loading/>
    </div>

    <div wire:loading.remove wire:target="setHistory">
        @if($history == 'index')
            <livewire:records.index :therapyId="$id"/>
        @elseif($history == 'sleep_diary')
            <livewire:records.sleep_diary :therapyId="$id"/>
        @elseif($history == 'identify_value')
            <livewire:records.identify_value :therapyId="$id"/>
        @elseif($history == 'thought_record')
            <livewire:records.thought_record :therapyId="$id"/>
        @elseif($history == 'emotion_record')
            <livewire:records.emotion_record :therapyId="$id"/>
        @elseif($history == 'committed_action')
            <livewire:records.committed_action :therapyId="$id"/>
        @elseif($history == 'chat')
            <livewire:records.chat :therapyId="$id"/>
        @elseif($history == 'schedule')
            <livewire:records.schedule :therapyId="$id"/>
        @endif
    </div>
</section>

