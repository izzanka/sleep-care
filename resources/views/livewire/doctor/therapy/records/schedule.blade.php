<?php

use App\Enum\TherapyStatus;
use App\Models\TherapySchedule;
use App\Service\TherapyScheduleService;
use App\Service\TherapyService;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;
    protected TherapyScheduleService $therapyScheduleService;

    public $date = null;
    public $time = null;
    public ?int $ID = null;
    public ?string $title = null;
    public ?string $link = null;
    public ?string $note = null;
    public bool $is_completed = false;

    public $therapy;
    public $therapySchedules;

    public function boot(TherapyService $therapyService, TherapyScheduleService $therapyScheduleService)
    {
        $this->therapyService = $therapyService;
        $this->therapyScheduleService = $therapyScheduleService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if (!$this->therapy) {
            return $this->redirectRoute('doctor.therapies.in_progress.index');
        }
        $this->therapySchedules = $this->therapyScheduleService->get($this->therapy->id);
        $this->is_completed = now()->greaterThan($this->therapy->end_date);
    }

    public function resetEdit()
    {
        $this->reset(['date', 'time', 'link', 'note']);
        $this->resetValidation(['date', 'link', 'time', 'note']);
    }

    public function editSchedule(int $scheduleID)
    {
        $schedule = $this->therapyScheduleService->getByID($scheduleID);

        if (!$schedule) {
            session()->flash('status', ['message' => 'Jadwal terapi tidak dapat ditemukan.', 'success' => false]);
        }

        $this->fillScheduleData($schedule);

        $this->modal('editSchedule')->show();
    }

    protected function fillScheduleData(TherapySchedule $schedule)
    {
        $this->ID = $schedule->id;
        $this->date = $schedule->date ? $schedule->date->toDateString() : null;
        $this->time = $schedule->time ? Carbon::parse($schedule->time)->format('H:i') : null;
        $this->link = $schedule->link;
        $this->title = $schedule->title;
        $this->note = $schedule->note;
        $this->is_completed = $schedule->is_completed;
    }

    public function updateSchedule(int $scheduleID)
    {
        $validated = $this->validate([
            'date' => ['required'],
            'time' => ['required'],
            'link' => ['nullable', 'url:https'],
            'note' => ['nullable', 'string'],
            'is_completed' => ['required', 'boolean'],
        ]);

        $schedule = $this->therapyScheduleService->getByID($scheduleID);

        if (!$schedule) {
            session()->flash('status', ['message' => 'Jadwal terapi tidak dapat ditemukan.', 'success' => false]);
        }

        $schedule->update($validated);

        session()->flash('status', ['message' => 'Jadwal terapi berhasil diubah.', 'success' => true]);
        $this->js('window.scrollTo({ top: 240, behavior: "smooth" });');
    }

    public function updateTherapy()
    {
        if($this->is_completed){
            $this->therapy->update([
                'status' => TherapyStatus::COMPLETED->value
            ]);
            $this->therapy->patient->update(['is_therapy_in_progress' => false]);
            session()->flash('status', ['message' => 'Berhasil mengubah status terapi menjadi selesai.', 'success' => true]);
            $this->redirectRoute('doctor.therapies.completed.index');
        }else{
            session()->flash('status', ['message' => 'Terapi belum dapat diselesaikan karena tanggal selesai belum terlewati.', 'success' => false]);
            $this->js('window.scrollTo({ top: 240, behavior: "smooth" });');
        }
    }
}; ?>

<section>
    <flux:callout icon="information-circle" class="mb-4" color="blue"
                  x-data="{ visible: localStorage.getItem('hideMessageSchedule') !== 'true' }"
                  x-show="visible"
                  x-init="$watch('visible', value => !value && localStorage.setItem('hideMessageSchedule', 'true'))">
        <flux:callout.heading>Diskusi Jadwal Sesi Terapi</flux:callout.heading>

        <flux:callout.text>
            Anda dapat berdiskusi dengan pasien mengenai waktu jadwal sesi terapi melalui fitur percakapan.
            <br><br>
            <flux:callout.link href="#" @click="visible = false">Jangan tampilkan lagi.</flux:callout.link>
        </flux:callout.text>
    </flux:callout>

    <flux:modal name="editSchedule" class="w-full max-w-md md:max-w-lg lg:max-w-xl p-4 md:p-6">
        <div class="space-y-6" x-data="{ showNote: false }" x-init="showNote = @json($is_completed)">
            <form wire:submit="updateSchedule({{$ID}})">
                <div>
                    <flux:heading size="lg">Ubah {{$title}}</flux:heading>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                    <flux:input wire:model="date" label="Tanggal" type="date"></flux:input>
                    <flux:input wire:model="time" label="Waktu" type="time"></flux:input>
                </div>

                <div class="mt-5">
                    <flux:input wire:model="link" label="Link video konferensi"></flux:input>
                </div>
                <div class="mt-5">
                    <flux:checkbox wire:model="is_completed" label="Telah dilaksanakan?" x-model="showNote"/>
                </div>
                <div class="mt-5" x-show="showNote">
                    <flux:textarea wire:model="note" label="Catatan hasil sesi terapi untuk pasien"></flux:textarea>
                </div>
                <div class="mt-5">
                    <flux:button type="submit" variant="primary" class="w-full">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
    @foreach($therapySchedules as $schedule)
        <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 mb-5 dark:border-transparent"
             x-data="{openTab: null}" wire:key="{{$schedule->id}}">
            <div class="flex items-center justify-between flex-wrap gap-y-2">
                <div class="flex items-center gap-x-3">
                    <flux:icon.video-camera></flux:icon.video-camera>
                    <flux:heading size="lg">{{$schedule->title}}</flux:heading>
                    <flux:badge size="sm"
                                color="{{$schedule->is_completed ? 'green' : 'zink'}}">{{$schedule->is_completed ? 'Sudah Dilaksanakan' : 'Belum Dilaksanakan'}}</flux:badge>
                </div>
                @if($therapy->status === TherapyStatus::IN_PROGRESS)
                    <flux:button variant="primary" size="xs" icon="pencil-square"
                                 wire:click="editSchedule({{$schedule->id}})"></flux:button>
                @endif
            </div>
            <div class="mt-5">
                @if($schedule->link)
                    <flux:input value="{{$schedule->link}}" readonly copyable/>
                @else
                    <flux:input value="-" disabled/>
                @endif
            </div>
            <div class="flex items-center gap-2 mt-4">
                {{--                <flux:icon.calendar class="size-5"></flux:icon.calendar>--}}
                @if($schedule->date)
                    <flux:heading>
                        {{$schedule->date->isoFormat('D MMMM Y') }}
                        ({{Carbon::parse($schedule->time)->format('H:i')}}
                        - {{Carbon::parse($schedule->time)->addHour()->format('H:i')}})
                    </flux:heading>
                @else
                    <flux:text>
                        Tanggal dan waktu belum ditentukan.
                    </flux:text>
                @endif
            </div>
            <div class="mt-4">
                <flux:button.group>
                    <flux:button @click="openTab = openTab === 'desc' ? null : 'desc'" variant="primary">
                        Panduan
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-4 h-4 transition-transform duration-300"
                            :class="openTab == 'desc' ? 'rotate-180' : ''"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </flux:button>
                    @if($schedule->note)
                        <flux:button @click="openTab = openTab === 'note' ? null : 'note'" variant="primary">
                            Catatan
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-4 h-4 transition-transform duration-300"
                                :class="openTab == 'note' ? 'rotate-180' : ''"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </flux:button>
                    @endif
                </flux:button.group>
            </div>
            <div x-show="openTab === 'desc'" x-transition.duration.200ms class="mt-4">
                <flux:heading>
                    Panduan:
                </flux:heading>
                <ul class="list-disc list-inside mt-2">
                    @foreach(json_decode($schedule->description) as $description)
                        <flux:text>
                            <li>
                                {{$description}}
                            </li>
                        </flux:text>
                    @endforeach
                </ul>
            </div>
            <div x-show="openTab === 'note'" x-transition.duration.200ms class="mt-4">
                <flux:heading>
                    Catatan hasil sesi terapi untuk pasien:
                </flux:heading>
                <flux:text class="mt-2">
                    {{$schedule->note}}
                </flux:text>
            </div>
        </div>
    @endforeach
    @if($therapy->status === TherapyStatus::IN_PROGRESS)
        <flux:button class="w-full" variant="danger" wire:click="updateTherapy" wire:confirm="Apakah anda ingin menyelesaikan terapi ini?" :disabled="!$is_completed">
            Selesaikan Terapi
        </flux:button>
    @endif
    {{--    </x-therapies.on-going-layout>--}}
</section>
