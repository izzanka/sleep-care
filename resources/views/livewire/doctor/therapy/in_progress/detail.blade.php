<?php

use App\Enum\Problem;
use App\Enum\TherapyStatus;
use App\Service\TherapyService;
use App\Service\UserService;
use Livewire\Volt\Component;

new class extends Component {
    public $therapy;
    public string $menu = 'schedule';
    public $problems;
    public bool $isOnline = false;
    public int $uncompletedScheduleCount = 0;
    public int $unreadChatCount = 0;
    public $unreadThoughtRecord;
    public $unreadSleepDiary;
    public $unreadCommittedAction;
    public $unreadEmotionRecord;
    public $unreadIdentifyValue;

    protected TherapyService $therapyService;
    protected UserService $userService;

    public function boot(TherapyService $therapyService, UserService $userService)
    {
        $this->therapyService = $therapyService;
        $this->userService = $userService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->get(doctorId: $doctorId, status: TherapyStatus::IN_PROGRESS->value, id: $therapyId)->first();
        if (!$this->therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);
            return $this->redirectRoute('doctor.therapies.in_progress.index');
        }
        $thoughtRecord = $this->therapy->thoughtRecords->first();
        $committedAction = $this->therapy->committedActions->first();
        $emotionRecord = $this->therapy->emotionRecords->first();
        $identifyValue = $this->therapy->identifyValues->first();

        $this->uncompletedScheduleCount = $this->therapy->schedules()->where('is_completed', false)->count();
        $this->unreadChatCount = $this->therapy->doctor->user->received()->where('therapy_id', $this->therapy->id)->whereNull('read_at')->count();
        $this->problems = $this->formatPatientProblems($this->therapy->patient->problems);
        $this->isOnline = $this->userService->getPatientOnlineStatus($this->therapy->patient_id);
        $this->unreadChatsCount = $this->therapy->doctor->user->received()->where('therapy_id', $this->therapy->id)->whereNull('read_at')->count();
        $this->unreadThoughtRecord = $thoughtRecord?->questionAnswers()?->whereNull('is_read')->exists();
        $this->unreadCommittedAction = $committedAction?->questionAnswers()?->whereNull('is_read')->exists();
        $this->unreadEmotionRecord = $emotionRecord?->questionAnswers()?->whereNull('is_read')->exists();
        $this->unreadIdentifyValue = $identifyValue?->questionAnswers()?->whereNull('is_read')->exists();
        $this->unreadSleepDiary = $this->therapy?->sleepDiaries()?->whereHas('questionAnswers', function ($query) {
            $query->whereNull('is_read');
        })->exists();
    }

    public function setMenu($value)
    {
        $this->menu = $value;
    }

    protected function formatPatientProblems(?string $problems)
    {
        if (!$problems) {
            return '-';
        }

        return collect(json_decode($problems))
            ->map(fn($problem) => Problem::tryFrom($problem)?->label() ?? $problem)
            ->implode(', ');
    }

    public function checkPatientOnlineStatus()
    {
        $this->isOnline = $this->userService->getPatientOnlineStatus($this->therapy->patient_id);
    }

}; ?>

<section class="w-full">
{{--    @include('partials.main-heading', ['title' => 'Detail Terapi'])--}}

    <div class="flex justify-between items-center">
        <flux:heading size="xl" level="1">Detail Terapi</flux:heading>
    </div>
    <flux:subheading size="lg" class="mb-6"></flux:subheading>
    <flux:separator variant="subtle"/>

    <flux:heading class="mb-2 mt-4">
        Informasi Pasien:
    </flux:heading>

    <div x-data="{ showDetails: false }" wire:poll.4s.visible="checkPatientOnlineStatus" class="w-full">
        <flux:callout color="zink" inline class="w-full">
            <flux:callout.heading class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 w-full">
                <div class="flex items-center flex-wrap gap-2">
                    <span class="text-base font-medium truncate max-w-[180px] sm:max-w-none">
                        {{ $therapy->patient->name }}
                    </span>
                        <flux:badge size="sm" :color="$isOnline ? 'blue' : 'zinc'" class="shrink-0">
                            {{ $isOnline ? 'Online' : 'Offline' }}
                        </flux:badge>
                        </div>
                    <button
                        @click="showDetails = !showDetails"
                        class="text-sm hover:underline text-blue-600 whitespace-nowrap px-2 py-1 -mr-2"
                    >
                        <flux:text x-text="showDetails ? 'Sembunyikan' : 'Tampilkan'" class="text-blue-600"></flux:text>
                    </button>
            </flux:callout.heading>

            <template x-if="showDetails">
                <div class="mt-2">
                    <flux:callout.text>
                        <div>
                            <div>
                                <flux:heading>Jenis Kelamin:</flux:heading>
                                <flux:text>
                                    {{ $therapy->patient->gender->label() }}
                                </flux:text>
                            </div>
                            <div class="mt-2 mb-2">
                                <flux:heading>Usia:</flux:heading>
                                <flux:text>
                                    {{ $therapy->patient->age }}
                                </flux:text>
                            </div>
                            <div>
                                <flux:heading>Masalah Lainnya:</flux:heading>
                                <flux:text>
                                    {{ $problems }}
                                </flux:text>
                            </div>
                        </div>
                    </flux:callout.text>
                </div>
            </template>
        </flux:callout>
    </div>

    <div class="flex flex-col gap-4 mb-4 mt-4">
        <div>
            <flux:radio.group variant="segmented" label="Menu:" wire:model="menu">
                <flux:radio
                    label="Jadwal Sesi Terapi {{$uncompletedScheduleCount ? '('.$uncompletedScheduleCount.')' : ''}}"
                    value="schedule"
                    wire:click="setMenu('schedule')"
                />
                <flux:radio
                    label="Percakapan {{$unreadChatCount ? '('.$unreadChatCount.')' : ''}}"
                    value="chat"
                    wire:click="setMenu('chat')"
                />
            </flux:radio.group>
        </div>

        <div>
            <flux:radio.group variant="segmented" label="Catatan:" wire:model="menu" size="sm">
                <flux:radio
                    label="Nilai {{$unreadIdentifyValue ? '(Baru)' : ''}}"
                    value="identify_value"
                    wire:click="setMenu('identify_value')"
                />
                <flux:radio
                    label="Tidur {{$unreadSleepDiary ? '(Baru)' : ''}}"
                    value="sleep_diary"
                    wire:click="setMenu('sleep_diary')"
                />
                <flux:radio
                    label="Pikiran {{$unreadThoughtRecord ? '(Baru)' : ''}}"
                    value="thought_record"
                    wire:click="setMenu('thought_record')"
                />
                <flux:radio
                    label="Emosi {{$unreadEmotionRecord ? '(Baru)' : ''}}"
                    value="emotion_record"
                    wire:click="setMenu('emotion_record')"
                />
                <flux:radio
                    label="Tindakan {{$unreadCommittedAction ? '(Baru)' : ''}}"
                    value="committed_action"
                    wire:click="setMenu('committed_action')"
                />
            </flux:radio.group>
        </div>
    </div>

    <div wire:loading wire:target="setMenu" class="flex justify-center py-8">
        <flux:icon.loading/>
    </div>

    <div wire:loading.remove wire:target="setMenu" class="w-full">
        @if($menu == 'chat')
            <livewire:doctor.therapy.records.chat :therapyId="$therapy->id"/>
        @elseif($menu == 'schedule')
            <livewire:doctor.therapy.records.schedule :therapyId="$therapy->id"/>
        @elseif($menu == 'identify_value')
            <livewire:doctor.therapy.records.identify_value :therapyId="$therapy->id"/>
        @elseif($menu == 'sleep_diary')
            <livewire:doctor.therapy.records.sleep_diary :therapyId="$therapy->id"/>
        @elseif($menu == 'emotion_record')
            <livewire:doctor.therapy.records.emotion_record :therapyId="$therapy->id"/>
        @elseif($menu == 'thought_record')
            <livewire:doctor.therapy.records.thought_record :therapyId="$therapy->id"/>
        @elseif($menu == 'committed_action')
            <livewire:doctor.therapy.records.committed_action :therapyId="$therapy->id"/>
        @endif
    </div>
</section>
