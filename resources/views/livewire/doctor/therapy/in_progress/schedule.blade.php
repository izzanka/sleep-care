<?php

use App\Enum\TherapyStatus;
use App\Models\Therapy;
use App\Models\TherapySchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component {
    public $date = null;
    public $time = null;
    public ?int $ID = null;
    public ?string $title = null;
    public ?string $link = null;
    public ?string $note = null;
    public bool $is_completed = false;

    public function with()
    {
        $therapy = $this->getOngoingTherapy();
        $schedules = $this->getTherapySchedules($therapy->id);

        return [
            'schedules' => $schedules,
        ];
    }

    protected function getOngoingTherapy()
    {
        return Therapy::where('status', TherapyStatus::IN_PROGRESS->value)->first();
    }

    protected function getTherapySchedules(int $therapyId)
    {
        return TherapySchedule::where('therapy_id', $therapyId)->get();
    }

    public function resetEdit()
    {
        $this->reset(['date', 'time', 'link', 'note']);
        $this->resetValidation(['date', 'link', 'time', 'note']);
    }

    public function editSchedule(int $scheduleID)
    {
        $schedule = $this->findScheduleById($scheduleID);

        if (!$schedule) {
            return;
        }

        $this->fillScheduleData($schedule);

        $this->modal('editSchedule')->show();
    }

    protected function findScheduleById(int $scheduleID)
    {
        $schedule = TherapySchedule::find($scheduleID);

        if (!$schedule) {
            Session::flash('status', ['message' => 'Jadwal terapi tidak dapat ditemukan.', 'success' => false]);
        }

        return $schedule;
    }

    protected function fillScheduleData(TherapySchedule $schedule)
    {
        $this->ID = $schedule->id;
        $this->date = $schedule->date->toDateString();
        $this->time = $schedule->time->format('H:i');
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

        $schedule = $this->findScheduleById($scheduleID);

        if (!$schedule) {
            return;
        }

        $schedule->update($validated);

        $this->modal('editSchedule')->close();

        Session::flash('status', ['message' => 'Jadwal terapi berhasil diubah.', 'success' => true]);

        $this->js("window.scrollTo({ top: 0, behavior: 'smooth' });");
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Jadwal'])
{{--    <x-therapies.on-going-layout>--}}
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
        @foreach($schedules as $schedule)
            <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 mb-5 dark:border-transparent"
                 x-data="{openTab: null}" wire:key="{{$schedule->id}}">
                <div class="flex items-center justify-between flex-wrap gap-y-2">
                    <div class="flex items-center gap-x-3">
                        <flux:icon.video-camera></flux:icon.video-camera>
                        <flux:heading size="lg">{{$schedule->title}}</flux:heading>
                        <flux:badge size="sm"
                                    color="{{$schedule->is_completed ? 'green' : 'zink'}}">{{$schedule->is_completed ? 'Sudah Dilaksanakan' : 'Belum Dilaksanakan'}}</flux:badge>
                    </div>
                    <flux:button size="xs" icon="pencil-square" wire:click="editSchedule({{$schedule->id}})"></flux:button>
                </div>
                <div class="mt-5">
                    @if($schedule->link)
                        <flux:input icon="link" value="{{$schedule->link}}" readonly copyable/>
                    @else
                        <flux:input icon="link" value="-" disabled/>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <flux:icon.clock></flux:icon.clock>
                    <flux:text>{{$schedule->date->format('d M Y')}} - {{$schedule->time->format('H:i')}}</flux:text>
                </div>
                <div class="mt-4">
                    <flux:button.group>
                        <flux:button @click="openTab = openTab === 'desc' ? null : 'desc'">
                            Deskripsi
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-4 h-4 transition-transform duration-300"
                                :class="openTab == 'desc' ? 'rotate-180' : ''"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </flux:button>
                        @if($schedule->note)
                            <flux:button @click="openTab = openTab === 'note' ? null : 'note'">
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
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </flux:button>
                        @endif
                    </flux:button.group>
                </div>
                <div x-show="openTab === 'desc'" x-transition.duration.200ms class="mt-4">
                    <flux:text>
                        Deskripsi:
                    </flux:text>
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
                    <flux:text>
                        Catatan:
                    </flux:text>
                    <flux:text class="mt-2">
                        {{$schedule->note}}
                    </flux:text>
                </div>
            </div>
        @endforeach

{{--    </x-therapies.on-going-layout>--}}
</section>
