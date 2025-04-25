<?php

use App\Enum\QuestionType;
use App\Service\ChartService;
use App\Service\Records\ThoughtRecordService;
use App\Service\TherapyService;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {

    protected ChartService $chartService;
    protected TherapyService $therapyService;
    protected ThoughtRecordService $thoughtRecordService;

    public $text;
    public $therapy;
    public $labels;

    public function boot(ChartService         $chartService,
                         TherapyService       $therapyService,
                         ThoughtRecordService $thoughtRecordService)
    {
        $this->chartService = $chartService;
        $this->therapyService = $therapyService;
        $this->thoughtRecordService = $thoughtRecordService;
    }

    public function mount()
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->getInprogress($doctorId);
        $this->thoughtRecords = $this->thoughtRecordService->get($this->therapy->id);
        $this->labels = $this->chartService->labels;
        $this->text = 'Frekuensi Kemunculan Pikiran';
    }

    private function extractQuestions()
    {
        return $this->thoughtRecords->questionAnswers
            ->pluck('question.question')
            ->unique()
            ->values();
    }

    private function countThoughtRecordDates($chunks)
    {
        return $chunks->map(function ($chunk) {
            return optional($chunk->keyBy(fn($qa) => $qa->question_id)[23]?->answer)->answer;
        })->filter()->countBy();
    }

    private function groupCountsByWeek($counts)
    {
        $startDate = $this->therapy->start_date;

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

    public function with()
    {

        $questions = $this->extractQuestions();
        $chunks = $this->thoughtRecords->questionAnswers->chunk(count($questions));
        $dateCounts = $this->countThoughtRecordDates($chunks);
        $weeklyData = $this->groupCountsByWeek($dateCounts);
        $maxValue = $this->chartService->calculateMaxValue($weeklyData);

        return [
            'thoughtRecordQuestions' => $questions,
            'chunks' => $chunks,
            'data' => $weeklyData->values()->toArray(),
            'maxValue' => $maxValue,
        ];
    }

}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Catatan Pikiran (Thought Record)'])

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
                                    {{ Carbon::parse($value)->format('d M') }}
                                @else
                                    @if(\Illuminate\Support\Str::isJson($value))
                                        @foreach(json_decode($value) as $txt)
                                            <div class="py-1">
                                                {{$txt}}
                                            </div>
                                        @endforeach
                                    @else
                                        {{ $value ?? '-' }}
                                    @endif
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
