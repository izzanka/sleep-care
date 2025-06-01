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
    public $rating;

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
        $this->therapy = $this->therapyService->get(doctorId: $doctorId, status: TherapyStatus::COMPLETED->value, id: $therapyId)->first();
        if (!$this->therapy) {
            session()->flash('status', ['message' => 'Terapi tidak ditemukan.', 'success' => false]);
            return $this->redirectRoute('doctor.therapies.in_progress.index');
        }
        $this->problems = $this->formatPatientProblems($this->therapy->patient->problems);
        $this->rating = $this->therapy->doctor->ratings()->where('user_id', $this->therapy->patient_id)->value('rating') ?? 0;
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

}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Detail Riwayat Terapi'])

    <flux:heading class="mb-2">
        Informasi Pasien:
    </flux:heading>

    <div x-data="{ showDetails: false }">
        <flux:callout icon="user" color="zink" inline>
            <flux:callout.heading class="flex items-center justify-between">
                <div>
                    {{ $therapy->patient->name }}
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
                    <flux:separator variant="subtle" class="mt-4 mb-4"/>
                    <flux:callout.text>
                        Ulasan:
                        @if($rating > 0)
                        <div class="flex mt-2 mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $rating)
                                    <flux:icon.star variant="solid" class="text-blue-600"></flux:icon.star>
                                @else
                                    <flux:icon.star class="text-gray-300"></flux:icon.star>
                                @endif
                            @endfor
                        </div>
                        <div>
                            {{$this->therapy->comment ?? '-'}}
                        </div>
                        @else
                            <div class="mt-2">
                                Belum ada ulasan
                            </div>
                        @endif
                    </flux:callout.text>
                </div>
            </template>

        </flux:callout>
    </div>

{{--    <flux:separator variant="subtle" class="mt-4 mb-4 "/>--}}

    {{--    @include('partials.main-heading', ['title' => 'Detail Terapi (' . $patientName . ')'])--}}

    <div class="flex flex-wrap gap-4 items-start mb-4 mt-4">
        <flux:radio.group variant="segmented" label="Menu:" wire:model="menu">
            {{--            <flux:radio--}}
            {{--                label="Informasi"--}}
            {{--                value="index"--}}
            {{--                wire:click="setMenu('index')"--}}
            {{--            />--}}
            <flux:radio
                label="Jadwal"
                value="schedule"
                wire:click="setMenu('schedule')"
            />
            <flux:radio
                label="Percakapan"
                value="chat"
                wire:click="setMenu('chat')"
            />
        </flux:radio.group>

        <flux:radio.group variant="segmented" label="Catatan:" wire:model="menu">
            <flux:radio
                label="Nilai "
                value="identify_value"
                wire:click="setMenu('identify_value')"
            />
            <flux:radio
                label="Tidur "
                value="sleep_diary"
                wire:click="setMenu('sleep_diary')"
            />
            <flux:radio
                label="Pikiran"
                value="thought_record"
                wire:click="setMenu('thought_record')"
            />
            <flux:radio
                label="Emosi "
                value="emotion_record"
                wire:click="setMenu('emotion_record')"
            />
            <flux:radio
                label="Tindakan"
                value="committed_action"
                wire:click="setMenu('committed_action')"
            />
        </flux:radio.group>
    </div>

{{--    <flux:separator variant="subtle" class="mb-4"/>--}}

    <div wire:loading wire:target="setMenu">
        <flux:icon.loading/>
    </div>

    <div wire:loading.remove wire:target="setMenu">
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
