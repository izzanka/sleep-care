<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\IdentifyValue;
use App\Models\Therapy;
use App\Models\ThoughtRecord;
use App\Service\ChartService;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {

    protected ChartService $chartService;

    public function boot(ChartService $chartService)
    {
        $this->chartService = $chartService;
    }

    public function with()
    {
        $doctorID = auth()->user()->doctor->id;

        $therapy = Therapy::where('doctor_id', $doctorID)
            ->where('status', TherapyStatus::IN_PROGRESS->value)
            ->firstOrFail();

        $thoughtRecords = ThoughtRecord::where('therapy_id', $therapy->id)
            ->firstOrFail();

        $questions = $this->extractQuestions($thoughtRecords);
        $chunks = $thoughtRecords->questionAnswers->chunk(count($questions));
        $dateCounts = $this->countThoughtRecordDates($chunks);
        $weeklyData = $this->groupCountsByWeek($therapy->start_date, $dateCounts);
        $maxValue = $this->chartService->calculateMaxValue($weeklyData);

        return [
            'thoughtRecords' => $thoughtRecords,
            'thoughtRecordQuestions' => $questions,
            'chunks' => $chunks,
            'labels' => $this->chartService->labels,
            'data' => $weeklyData->values()->toArray(),
            'maxValue' => $maxValue,
            'text' => 'Frekuensi',
        ];
    }

    private function extractQuestions($thoughtRecords)
    {
        return $thoughtRecords->questionAnswers
            ->pluck('question.question')
            ->unique()
            ->values();
    }

    private function countThoughtRecordDates($chunks)
    {
        return $chunks->map(function ($chunk) {
            return optional($chunk->keyBy(fn($qa) => $qa->question->question)['Tanggal']?->answer)->answer;
        })->filter()->countBy();
    }

    private function groupCountsByWeek($startDate, $counts)
    {
        $weeks = collect(range(1, 6))->mapWithKeys(fn($week) => ["Minggu $week" => 0]);

        $counts->each(function ($count, $date) use ($startDate, &$weeks) {
            $dayDiff = $startDate->diffInDays($date, false);
            if ($dayDiff >= 0) {
                $week = intdiv($dayDiff, 7) + 1;
                if ($week >= 1 && $week <= 6) {
                    $weeks["Minggu $week"] += $count;
                }
            }
        });

        return $weeks;
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Thought Record'])

    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="relative w-full">
            <canvas id="thoughtRecordChart" class="w-full h-full"></canvas>
        </div>

        <flux:separator class="mt-4 mb-4"/>

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
                        min: 0,
                        max: @json($maxValue),
                        ticks: {
                            precision: 0,
                            stepSize: 1,
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
