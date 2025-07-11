<?php

use App\Enum\TherapyStatus;
use App\Enum\UserRole;
use App\Models\TherapySchedule;
use App\Notifications\CompletedTherapy;
use App\Service\TherapyScheduleService;
use App\Service\TherapyService;
use App\Service\UserService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;
    protected TherapyScheduleService $therapyScheduleService;
    protected UserService $userService;

    public $date = null;
    public $time = null;
    public ?int $ID = null;
    public ?string $title = null;
    public ?string $link = null;
    public ?string $note = null;
    public bool $is_completed = false;
    public int $number;
    public $weekStart;
    public $weekEnd;
    public $weekString;

    public $therapy;
    public $therapySchedules;

    public function boot(TherapyService $therapyService, TherapyScheduleService $therapyScheduleService, UserService $userService)
    {
        $this->therapyService = $therapyService;
        $this->therapyScheduleService = $therapyScheduleService;
        $this->userService = $userService;
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

        $this->number = (int)explode('ke-', $this->title)[1];
        $this->weekStart = $this->therapy->start_date->addWeeks($this->number - 1);
        $this->weekEnd = $this->weekStart->addDays(6);
        $this->weekString = ' (' . $this->weekStart->isoFormat('D MMMM') . ' - ' . $this->weekEnd->isoFormat('D MMMM') . ')';
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

        if (!Carbon::parse($validated['date'])->between($this->weekStart, $this->weekEnd)) {
            throw ValidationException::withMessages([
                'date' => 'Tanggal invalid.',
            ]);
        } else {
            $schedule = $this->therapyScheduleService->getByID($scheduleID);

            if (!$schedule) {
                session()->flash('status', ['message' => 'Jadwal sesi terapi tidak dapat ditemukan.', 'success' => false]);
            }

            $schedule->update($validated);

            session()->flash('status', ['message' => 'Jadwal sesi terapi berhasil diubah.', 'success' => true]);
            $this->redirectRoute('doctor.therapies.in_progress.detail', $this->therapy->id);
        }
    }

    public function updateTherapy()
    {
        if ($this->is_completed) {
            $this->therapy->update([
                'status' => TherapyStatus::COMPLETED->value
            ]);

            if($this->therapy->doctor->therapies()->where('status', TherapyStatus::IN_PROGRESS->value)->count() === 0){
                $this->therapy->doctor->user->update(['is_therapy_in_progress' => false]);
            }

            $this->therapy->patient->update(['is_therapy_in_progress' => false]);
            $adminUser = $this->userService->get(role: UserRole::ADMIN->value)->first();

            $adminUser->notify(new CompletedTherapy($this->therapy));
            $this->therapy->doctor->user->notify(new CompletedTherapy($this->therapy));

            session()->flash('status', ['message' => 'Berhasil mengubah status terapi menjadi selesai.', 'success' => true]);
            $this->redirectRoute('doctor.therapies.completed.index');
        } else {
            session()->flash('status', ['message' => 'Terapi belum dapat diselesaikan karena tanggal selesai belum terlewati.', 'success' => false]);
            $this->js('window.scrollTo({ top: 240, behavior: "smooth" });');
        }
    }
}; ?>

<section class="w-full">
    @include('partials.main-heading', ['title' => null])

    @if($therapy->status === TherapyStatus::IN_PROGRESS)
        <flux:callout icon="information-circle" class="mb-4" color="blue"
                      x-data="{ visible: localStorage.getItem('hideMessageSchedule') !== 'true' }"
                      x-show="visible"
                      x-init="$watch('visible', value => !value && localStorage.setItem('hideMessageSchedule', 'true'))">
            <flux:callout.heading>Diskusi Jadwal Sesi Terapi</flux:callout.heading>
            <flux:callout.text>
                Anda dapat berdiskusi dengan pasien mengenai waktu jadwal sesi terapi melalui fitur percakapan.
                <br><br>
                <flux:callout.link href="#" @click="visible = false">
                    Jangan tampilkan lagi.
                </flux:callout.link>
            </flux:callout.text>
        </flux:callout>
    @endif
    <!-- Callout Message -->


    <!-- Edit Schedule Modal -->
    <flux:modal name="editSchedule" class="w-full max-w-[95vw] sm:max-w-md md:max-w-lg lg:max-w-xl p-4 md:p-6">
        <div class="space-y-4 md:space-y-6" x-data="{ showNote: false }" x-init="showNote = @json($is_completed)">
            <form wire:submit="updateSchedule({{$ID}})">
                <div>
                    <flux:heading size="lg">Ubah {{$title}} {{$weekString}}</flux:heading>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mt-4 mb-4">
                    <div>
                        <flux:input wire:model="date" label="Tanggal" type="date"/>
                    </div>
                    <div>
                        <flux:input wire:model="time" label="Waktu Mulai (Durasi 1 Jam)" type="time"/>
                    </div>
                </div>

                <div class="mt-4 sm:mt-5">
                    <flux:input wire:model="link" label="Link video konferensi"></flux:input>
                </div>
                <div class="mt-4 sm:mt-5">
                    <flux:checkbox wire:model="is_completed" label="Telah dilaksanakan?" x-model="showNote"/>
                </div>
                <div class="mt-4 sm:mt-5" x-show="showNote">
                    <flux:textarea wire:model="note" label="Catatan hasil sesi terapi untuk pasien"></flux:textarea>
                </div>
                <div class="mt-4 sm:mt-5">
                    <flux:button type="submit" variant="primary" class="w-full">Simpan</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Schedule List -->
    @foreach($therapySchedules as $schedule)
        <div class="relative rounded-lg px-4 sm:px-6 py-4 bg-white border dark:bg-zinc-700 mb-4 sm:mb-5 dark:border-transparent"
             x-data="{openTab: null}" wire:key="{{$schedule->id}}">
            <div class="flex items-center justify-between flex-wrap gap-y-2">
                <div class="flex items-center gap-x-2 sm:gap-x-3 flex-wrap">
                    <flux:icon.video-camera class="shrink-0"></flux:icon.video-camera>
                    <flux:heading class="whitespace-nowrap">{{$schedule->title}}</flux:heading>
                    <flux:badge size="sm"
                                color="{{$schedule->is_completed ? 'green' : 'zink'}}"
                                class="shrink-0">
                        {{$schedule->is_completed ? 'Sudah Dilaksanakan' : 'Belum Dilaksanakan'}}
                    </flux:badge>
                </div>
                @if($therapy->status === TherapyStatus::IN_PROGRESS)
                    <flux:button variant="primary" size="xs" icon="pencil-square"
                                 wire:click="editSchedule({{$schedule->id}})" class="shrink-0"></flux:button>
                @endif
            </div>
            <div class="mt-3 sm:mt-4">
                @if($schedule->link)
                    <flux:input value="{{$schedule->link}}" readonly copyable class="w-full"/>
                @else
                    <flux:input value="-" disabled class="w-full"/>
                @endif
            </div>
            <div class="flex items-center gap-2 mt-3 sm:mt-4">
                @if($schedule->date)
                    <flux:text>
                        {{$schedule->date->isoFormat('D MMMM Y') }}
                        ({{Carbon::parse($schedule->time)->format('H:i')}}
                        - {{Carbon::parse($schedule->time)->addHour()->format('H:i')}})
                    </flux:text>
                @else
                    <flux:text>
                        Tanggal dan waktu belum ditentukan.
                    </flux:text>
                @endif
            </div>
            <div class="mt-3 sm:mt-4">
                <flux:button.group class="flex flex-wrap">
                    <flux:button @click="openTab = openTab === 'desc' ? null : 'desc'" variant="primary" size="sm" class="flex-1 sm:flex-none">
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
                        <flux:button @click="openTab = openTab === 'note' ? null : 'note'" variant="primary" size="sm" class="flex-1 sm:flex-none">
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
            <div x-show="openTab === 'desc'" x-transition.duration.200ms class="mt-3 sm:mt-4">
                <flux:heading size="md">
                    Panduan:
                </flux:heading>
                <ul class="list-disc list-inside mt-1 sm:mt-2 space-y-1">
                    @foreach(json_decode($schedule->description) as $description)
                        <flux:text class="leading-snug">
                            <li>
                                {{$description}}
                            </li>
                        </flux:text>
                    @endforeach
                </ul>
            </div>
            <div x-show="openTab === 'note'" x-transition.duration.200ms class="mt-3 sm:mt-4">
                <flux:heading size="md">
                    Catatan hasil sesi terapi untuk pasien:
                </flux:heading>
                <flux:text class="mt-1 sm:mt-2">
                    {{$schedule->note}}
                </flux:text>
            </div>
        </div>
    @endforeach

    <!-- Complete Therapy Button -->
    @if($therapy->status === TherapyStatus::IN_PROGRESS)
        <flux:button class="w-full mt-4" variant="danger" wire:click="updateTherapy"
                     wire:confirm="Apakah anda ingin menyelesaikan terapi ini?" :disabled="!$is_completed">
            Selesaikan Terapi
        </flux:button>
    @endif
</section>
