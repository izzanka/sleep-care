<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\SleepDiary;
use App\Models\Therapy;
use Livewire\Volt\Component;

new class extends Component {
    public function with()
    {
        $doctorID = auth()->user()->load('doctor')->doctor->id;
        $therapy = Therapy::where('doctor_id', $doctorID)->with('patient')->where('status', TherapyStatus::IN_PROGRESS->value)->first();
        $sleepDiaries = SleepDiary::with(['questionAnswers.question', 'questionAnswers.answer'])
            ->where('therapy_id', $therapy->id)
            ->orderBy('week')
            ->orderBy('day')
            ->get()
            ->groupBy('week');

        $questions = $sleepDiaries
            ->flatten(1)
            ->pluck('questionAnswers')
            ->flatten()
            ->pluck('question')
            ->unique('id');

        return [
            'sleepDiaries' => $sleepDiaries,
            'questions' => $questions,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Sleep Diary'])
    <x-therapies.on-going-layout>
        @foreach($sleepDiaries as $index => $sleepDiary)
            <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 mb-5 dark:border-transparent"
                 x-data="{showTable: false}">
                <div class="flex items-center w-full">
                    <flux:icon.calendar class="mr-2"/>
                    <flux:button variant="ghost" class="w-full" @click="showTable = !showTable">
                        <div class="flex items-center justify-between w-full">
                            Sleep Diary Minggu Ke-{{$index}}
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-4 h-4 transition-transform duration-300"
                                :class="showTable ? 'rotate-180' : ''"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </div>
                    </flux:button>
                </div>
                <div x-show="showTable" x-transition.duration.200ms class="mt-4">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm border">
                            <thead>
                                <tr>
                                    <th class="border p-2 text-center">Hari</th>
                                    <th class="border p-2 text-center">Senin</th>
                                    <th class="border p-2 text-center">Selasa</th>
                                    <th class="border p-2 text-center">Rabu</th>
                                    <th class="border p-2 text-center">Kamis</th>
                                    <th class="border p-2 text-center">Jumat</th>
                                    <th class="border p-2 text-center">Sabtu</th>
                                    <th class="border p-2 text-center">Minggu</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th class="border p-2 text-center">Tanggal</th>
                                @foreach($sleepDiary as $diary)
                                    <th class="border p-2 text-center">{{ $diary->dayAndMonth }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="p-2 text-center font-bold" colspan="8">Siang Hari</td>
                            </tr>
                            @foreach($questions as $question)
                                <tr>
                                    <td class="border p-2 text-center">{{$question->question}}</td>
                                    @foreach($sleepDiary as $diary)
                                        @php
                                            $entry = $diary->questionAnswers->firstWhere('question_id', $question->id);
                                        @endphp
                                        <td class="border p-2">
                                            <div class="flex justify-center items-center h-full">
                                                @if($entry->answer->type == QuestionType::BINARY->value)
                                                    @if($entry->answer->answer)
                                                        <flux:icon.check-circle class="text-green-500"></flux:icon.check-circle>
                                                    @else
                                                        <flux:icon.x-circle class="text-red-500"></flux:icon.x-circle>
                                                    @endif
                                                @else
                                                    {{$entry->answer->answer}}
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach

                                </tr>
                                @if($question->id == 13)
                                    <tr>
                                        <td class="p-2 text-center font-bold" colspan="8">Malam Hari</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </x-therapies.on-going-layout>
</section>
