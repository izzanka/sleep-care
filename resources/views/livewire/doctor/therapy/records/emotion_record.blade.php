<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\EmotionRecord;
use App\Models\Therapy;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    public function with()
    {
        $doctorId = auth()->user()->loadMissing('doctor')->doctor->id;

        $therapy = Therapy::with('patient')
            ->where('doctor_id', $doctorId)
            ->where('status', TherapyStatus::IN_PROGRESS->value)
            ->first();

        $emotionRecords = EmotionRecord::with(['questionAnswers.question', 'questionAnswers.answer'])
            ->where('therapy_id', $therapy->id)
            ->first();

        $questions = $emotionRecords->questionAnswers
            ->pluck('question.question')
            ->unique()
            ->values();

        $rows = $emotionRecords->questionAnswers->chunk($questions->count());

        return [
            'therapy' => $therapy,
            'questions' => $questions,
            'rows' => $rows,
            'labels' => ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5', 'Minggu 6'],
            'text' => 'Frekuensi',
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Emotion Record'])

    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="relative w-full">
            <canvas id="emotionRecordChart" class="w-full h-full"></canvas>
        </div>

        <flux:separator class="mt-4 mb-4"/>

        <div class="overflow-x-auto">
            <table class="text-sm border mb-2 mt-2 w-full">
                <thead>
                <tr>
                    <th class="border p-2 text-center">No</th>
                    @foreach($questions as $question)
                        <th class="border p-2 text-center">{{ $question }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $index => $row)
                    <tr>
                        <td class="border p-2 text-center">{{ $index + 1 }}</td>
                        @foreach($questions as $question)
                            @php
                                $answer = $row->firstWhere('question.question', $question);
                            @endphp
                            <td class="border p-2 text-center">
                                @if($answer?->answer?->type === QuestionType::DATE->value)
                                    {{ Carbon::parse($answer->answer->answer)->format('d/m/Y') }}
                                @else
                                    {{ $answer->answer->answer ?? '-' }}
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
        const canvas = document.getElementById('emotionRecordChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');

        const data = {
            labels: @json($labels),
            datasets: [
                {
                    label: 'Marah',
                    data: [1, 2, 3, 4, 5, 6],
                    borderWidth: 1,
                },
                {
                    label: 'Sedih',
                    data: [6, 5, 4, 3, 2, 1],
                    borderWidth: 1,
                },
                {
                    label: 'Frustasi',
                    data: [1, 2, 3, 4, 5, 6],
                    borderWidth: 1,
                },
                {
                    label: 'Malu',
                    data: [1, 2, 3, 4, 5, 6],
                    borderWidth: 1,
                },
                {
                    label: 'Depresi',
                    data: [1, 2, 3, 4, 5, 6],
                    borderWidth: 1,
                },
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
                            color: isDark ? '#ffffff' : '#000000',
                            stepSize: 1,
                            min: 0,
                            max: 10
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
