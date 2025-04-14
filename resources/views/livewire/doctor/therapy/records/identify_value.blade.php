<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\IdentifyValue;
use App\Models\Therapy;
use Livewire\Volt\Component;

new class extends Component {
    public function with()
    {
        $doctorId = auth()->user()->loadMissing('doctor')->doctor->id;

        $therapy = Therapy::with('patient')
            ->where('doctor_id', $doctorId)
            ->where('status', TherapyStatus::IN_PROGRESS->value)
            ->first();

        $identifyValues = IdentifyValue::with(['questionAnswers.question', 'questionAnswers.answer'])
            ->where('therapy_id', $therapy->id)
            ->first();

        $questions = $identifyValues->questionAnswers
            ->pluck('question.question')
            ->map(fn($q) => explode(',', $q)[0])
            ->unique()
            ->values()
            ->toArray();

        $labels = $identifyValues->questionAnswers
            ->pluck('answer.note')
            ->filter()
            ->unique()
            ->values();

        $numberAnswers = collect($identifyValues->questionAnswers)
            ->filter(fn($qa) => $qa->answer->type === QuestionType::NUMBER->value)
            ->groupBy(fn($qa) => explode(',', $qa->question->question)[0])
            ->map(fn($group) => $group->pluck('answer.answer')->map(fn($val) => (int) $val))
            ->toArray();

        $textAnswers = collect($identifyValues->questionAnswers)
            ->filter(fn($qa) => $qa->answer->type === QuestionType::TEXT->value)
            ->groupBy(fn($qa) => explode(',', $qa->question->question)[0])
            ->map(fn($group) => $group->pluck('answer.answer'))
            ->toArray();

        return [
            'datasetLabels' => $questions,
            'labels' => $labels,
            'numberAnswers' => $numberAnswers,
            'textAnswers' => $textAnswers,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Identify Value'])

    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="relative w-full max-w-md mx-auto">
            <canvas id="identifyValueChart" class="w-full h-full"></canvas>
        </div>

        <flux:separator class="mt-4 mb-4" />

        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm border mb-2 mt-2">
                <thead>
                <tr>
                    <th class="border p-2 text-center">No</th>
                    <th class="border p-2 text-center">Area</th>
                    <th class="border p-2 text-center">{{ $datasetLabels[1] ?? '-' }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($labels as $index => $label)
                    <tr>
                        <td class="border p-2 text-center">{{ $loop->iteration }}</td>
                        <td class="border p-2">{{ $label }}</td>
                        <td class="border p-2">
                            {{ $textAnswers[$datasetLabels[1]][$index] ?? '-' }}
                        </td>
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
        const canvas = document.getElementById('identifyValueChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');

        const data = {
            labels: @json($labels),
            datasets: [
                {
                    label: @json($datasetLabels[0]),
                    data: @json($numberAnswers[$datasetLabels[0]]),
                    fill: true,
                },
                {
                    label: @json($datasetLabels[2]),
                    data: @json($numberAnswers[$datasetLabels[2]]),
                    fill: true,
                }
            ]
        };

        const config = {
            type: 'radar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Kepentingan dan Kesesuaian',
                        color: isDark ? '#ffffff' : '#000000',
                    },
                    legend: {
                        labels: {
                            color: isDark ? '#ffffff' : '#000000',
                        }
                    }
                },
                scales: {
                    r: {
                        beginAtZero: true,
                        min: 0,
                        max: 10,
                        ticks: {
                            stepSize: 1,
                            color: isDark ? '#ffffff' : '#000000',
                        },
                        grid: {
                            color: isDark ? '#ffffff' : '#000000',
                        },
                        pointLabels: {
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
