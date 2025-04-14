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

        $therapy = Therapy::where('doctor_id', $doctorID)
            ->with('patient')
            ->where('status', TherapyStatus::IN_PROGRESS->value)
            ->first();

        $thoughtRecords = ThoughtRecord::with(['questionAnswers.question', 'questionAnswers.answer'])
            ->where('therapy_id', $therapy->id)
            ->first();

        $thoughtRecordQuestions = $thoughtRecords->questionAnswers
            ->pluck('question.question')
            ->unique()
            ->values();

        $labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5', 'Minggu 6'];
        $data = [7, 6, 2, 1, 4, 0];
        $text = 'Frekuensi';

        $chunks = $thoughtRecords->questionAnswers->chunk(count($thoughtRecordQuestions));

        return [
            'thoughtRecords' => $thoughtRecords,
            'thoughtRecordQuestions' => $thoughtRecordQuestions,
            'chunks' => $chunks,
            'labels' => $labels,
            'data' => $data,
            'text' => $text,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Thought Record'])

    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        {{-- Chart --}}
        <div class="relative w-full">
            <canvas id="thoughtRecordChart" class="w-full h-full"></canvas>
        </div>

        <flux:separator class="mt-4 mb-4"/>

        {{-- Thought Record Table --}}
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
                                $value = $answer?->answer?->answer;
                                $type = $answer?->answer?->type;
                            @endphp
                            <td class="border p-2 text-center">
                                @if($type === QuestionType::DATE->value && $value)
                                    {{ Carbon::parse($value)->format('d/m/Y') }}
                                @else
                                    {{ $value ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

@script
<script>
    let chartInstance;

    function createChart() {
        const canvas = document.getElementById('thoughtRecordChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');

        const data = {
            labels: @json($labels),
            datasets: [
                {
                    label: 'Total',
                    data: @json($data),
                    borderWidth: 1,
                }
            ],
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: @json($text),
                        color: isDark ? '#ffffff' : '#000000',
                    },
                    legend: {
                        labels: {
                            color: isDark ? '#ffffff' : '#000000',
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: isDark ? '#ffffff' : '#000000',
                        },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            stepSize: 1,
                            min: 0,
                            max: 10,
                            color: isDark ? '#ffffff' : '#000000',
                        }
                    }
                }
            }
        };

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, config);
    }

    document.addEventListener('DOMContentLoaded', () => {
        createChart();
    });

    const observer = new MutationObserver(() => {
        createChart();
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
</script>

@endscript
