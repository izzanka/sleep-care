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

<section>
    @include('partials.main-heading', ['title' => 'Detail Terapi'])

{{--    <div>--}}
{{--        <flux:button icon="arrow-uturn-left" class="mb-4" size="sm" href="{{route('doctor.therapies.in_progress.index')}}" variant="primary">--}}
{{--            Kembali--}}
{{--        </flux:button>--}}
{{--    </div>--}}

    <flux:heading class="mb-2">
        Informasi Pasien:
    </flux:heading>
    <div
        x-data="{ showDetails: false }"
        wire:poll.4s.visible="checkPatientOnlineStatus"
    >
        <flux:callout icon="user" color="zink" inline>
            <flux:callout.heading class="flex items-center justify-between">
                <div>
                    {{ $therapy->patient->name }}
                    <flux:badge size="sm" :color="$isOnline ? 'blue' : 'zinc'" class="ml-2">
                        {{ $isOnline ? 'Online' : 'Offline' }}
                    </flux:badge>
                </div>
                <button
                    @click="showDetails = !showDetails"
                    class="text-sm hover:underline text-blue-600"
                >
                    <flux:text x-text="showDetails ? 'Sembunyikan' : 'Tampilkan'" class="text-blue-600"></flux:text>
                </button>
            </flux:callout.heading>


            <template x-if="showDetails">
                <div class="mt-2">
                    <flux:callout.text>
                        <div>
                            Jenis Kelamin: {{ $therapy->patient->gender->label() }}
                        </div>
                        <div class="mt-2">
                            Usia: {{ $therapy->patient->age }}
                        </div>
                        <div class="mt-2">
                            Masalah Lainnya: {{ $problems }}
                        </div>
                    </flux:callout.text>
                </div>
            </template>
        </flux:callout>
    </div>

{{--        <flux:separator class="mt-4 mb-4 "/>--}}

    {{--    @include('partials.main-heading', ['title' => 'Detail Terapi (' . $patientName . ')'])--}}

    <div class="flex flex-wrap gap-4 items-start mb-4 mt-4">
        <flux:radio.group variant="segmented" label="Menu:" wire:model="menu">
            {{--            <flux:radio--}}
            {{--                label="Informasi"--}}
            {{--                value="index"--}}
            {{--                wire:click="setMenu('index')"--}}
            {{--            />--}}
            <flux:radio
                label="Jadwal {{$uncompletedScheduleCount ? '('.$uncompletedScheduleCount.')' : ''}}"
                value="schedule"
                wire:click="setMenu('schedule')"
            />
            <flux:radio
                label="Percakapan {{$unreadChatCount ? '('.$unreadChatCount.')' : ''}}"
                value="chat"
                wire:click="setMenu('chat')"
            />
        </flux:radio.group>

        <flux:radio.group variant="segmented" label="Catatan:" wire:model="menu">
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

{{--    <flux:separator class="mb-4"/>--}}

    <div wire:loading wire:target="setMenu">
        <flux:icon.loading/>
    </div>

    <div wire:loading.remove wire:target="setMenu">
        {{--        @if($menu == 'index')--}}
        {{--            <livewire:doctor.therapy.records.index :therapyId="$therapyId"/>--}}
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
