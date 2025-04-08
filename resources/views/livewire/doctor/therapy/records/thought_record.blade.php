<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\IdentifyValue;
use App\Models\Therapy;
use App\Models\ThoughtRecord;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    public function with()
    {
        $doctorID = auth()->user()->load('doctor')->doctor->id;
        $therapy = Therapy::where('doctor_id', $doctorID)->with('patient')->where('status', TherapyStatus::IN_PROGRESS->value)->first();
        $thoughtRecords = ThoughtRecord::with(['questionAnswers.question', 'questionAnswers.answer'])
            ->where('therapy_id', $therapy->id)->first();

        $thoughtRecordQuestions = $thoughtRecords->questionAnswers
            ->pluck('question.question')
            ->unique()
            ->values();

        $chunks = $thoughtRecords->questionAnswers->chunk(count($thoughtRecordQuestions));

        return [
            'thoughtRecords' => $thoughtRecords,
            'thoughtRecordQuestions' => $thoughtRecordQuestions,
            'chunks' => $chunks,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Thought Record'])
    <x-therapies.on-going-layout>
        <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
            <div class="overflow-x-auto">
                <table class="table-auto w-full text-sm border mb-2 mt-2">
                    <thead>
                        <tr>
                            <th class="border p-2 text-center">No</th>
                            @foreach($thoughtRecordQuestions as $question)
                                <th class="border p-2 text-center">{{ $question }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($chunks as $index => $chunk)
                        <tr>
                            <td class="border p-2 text-center">{{ $index + 1 }}</td>
                            @foreach($thoughtRecordQuestions as $header)
                                @php
                                    $answer = $chunk->firstWhere('question.question', $header);
                                @endphp
                                <td class="border p-2">
                                    @if($answer->answer->type == QuestionType::DATE->value)
                                        {{Carbon::parse($answer->answer->answer)->format('d/m/Y')}}
                                    @else
                                        {{ $answer->answer->answer }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-therapies.on-going-layout>
</section>
