<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Service\ChartService;
use App\Service\Records\EmotionRecordService;
use App\Service\TherapyService;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    protected ChartService $chartService;
    protected TherapyService $therapyService;
    protected EmotionRecordService $emotionRecordService;

    public $therapy;
    public $emotionRecord;
    public $labels;
    public $chartTitle;
    public $selectedWeek;

    public function boot(ChartService         $chartService,
                         TherapyService       $therapyService,
                         EmotionRecordService $emotionRecordService)
    {
        $this->chartService = $chartService;
        $this->emotionRecordService = $emotionRecordService;
        $this->therapyService = $therapyService;
    }

    public function mount()
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->find(doctorId: $doctorId, status: TherapyStatus::IN_PROGRESS->value);
        $this->emotionRecord = $this->emotionRecordService->get($this->therapy->id);
        $this->labels = $this->chartService->labels;
        $this->selectedWeek = min((int) $this->therapy->start_date->diffInWeeks(now()) + 1, 6);
        $this->chartTitle = 'Frekuensi Kemunculan Emosi';
    }

    private function extractAnswerData($rows)
    {
        $results = collect();

        foreach ($rows as $row) {
            $groupedAnswers = $row->keyBy(fn($qa) => $qa->question_id);
            $date = Carbon::parse($groupedAnswers[27]->answer->answer);
            $emotion = $groupedAnswers[31]->answer->answer;

            $weekNumber = (int)$this->therapy->start_date->diffInWeeks($date) + 1;

            $results->push([
                'emotion' => $emotion,
                'week' => min($weekNumber, 6),
            ]);
        }

        return $results;
    }

    private function calculateWeeklyEmotionFrequencies($data)
    {

        return $data->groupBy('emotion')->map(function ($group) {
            $weeklyCounts = array_fill(1, 6, 0);
            foreach ($group as $entry) {
                $weeklyCounts[$entry['week']]++;
            }
            return collect($weeklyCounts)->values();
        });
    }

    private function buildChartDatasets($emotionCounts)
    {
        return $emotionCounts->map(function ($values, $label) {
            return [
                'label' => $label,
                'data' => $values,
                'borderWidth' => 1,
            ];
        })->values();
    }

    public function with()
    {
        $questions = $this->emotionRecord->questionAnswers
            ->pluck('question.question')
            ->unique()
            ->values();

        $chunks = $this->emotionRecord->questionAnswers->chunk(count($questions))
            ->sortByDesc(fn($chunk) => Carbon::parse($chunk->keyBy(fn($qa) => $qa->question_id)[27]->answer->answer))
            ->values();

        $filteredRows = $chunks->filter(function ($chunk) {
            $groupedAnswers = $chunk->keyBy(fn($qa) => $qa->question_id);
            $date = Carbon::parse($groupedAnswers[27]->answer->answer);
            $weekNumber = (int)$this->therapy->start_date->diffInWeeks($date) + 1;
            return min($weekNumber, 6) == $this->selectedWeek;
        })->values();

        $answerData = $this->extractAnswerData($chunks);
        $emotionFrequencies = $this->calculateWeeklyEmotionFrequencies($answerData);
        $chartDatasets = $this->buildChartDatasets($emotionFrequencies);
        $maxValue = $this->chartService->calculateMaxValue($emotionFrequencies->flatten()->toArray());

        return [
            'questions' => $questions,
            'answerRows' => $filteredRows,
            'datasets' => $chartDatasets,
            'maxValue' => $maxValue,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Catatan Emosi (Emotion Record)'])

    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="flex">
            <div class="w-full flex-shrink-0">
                <canvas id="emotionRecordChart" class="w-full h-80 mb-4"></canvas>
            </div>
        </div>
        {{--        <div class="relative w-full">--}}
        {{--            <canvas id="emotionRecordChart" class="w-full h-full"></canvas>--}}
        {{--        </div>--}}

        <flux:separator class="mt-4 mb-4"/>

            <flux:select wire:model.live="selectedWeek" label="Pilih Minggu" class="flex items-center justify-end mb-4">
                @for ($i = 1; $i <= 6; $i++)
                    <flux:select.option :value="$i">Minggu {{$i}}</flux:select.option>
                @endfor
            </flux:select>
{{--            <label for="week-select" class="mr-2 font-medium">Pilih Minggu:</label>--}}
{{--            <select id="week-select" wire:model.live="selectedWeek" class="border rounded p-2 dark:bg-zinc-700 dark:text-white">--}}
{{--                @for ($i = 1; $i <= 6; $i++)--}}
{{--                    <option value="{{ $i }}">Minggu {{ $i }}</option>--}}
{{--                @endfor--}}
{{--            </select>--}}

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
                @forelse($answerRows as $index => $row)
                    <tr>
                        <td class="border p-2 text-center">{{ $index + 1 }}</td>
                        @foreach($questions as $question)
                            @php
                                $answer = $row->firstWhere('question.question', $question)->answer;
                                $type = $answer->type;
                                $value = $answer->answer;
                                $formattedValue = match($type) {
                                    QuestionType::DATE->value => Carbon::parse($value)->format('d M'),
                                    QuestionType::TIME->value,QuestionType::NUMBER->value => $value,
                                    default => $value ?? '-',
                                };
                                $alignment = in_array($type, [QuestionType::DATE->value, QuestionType::TIME->value, QuestionType::NUMBER->value]) ? 'text-center' : 'text-left';
                            @endphp
                            <td class="border p-2 {{ $alignment }}">
                                {{ $formattedValue }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="border p-2 text-center" colspan="9">
                            <flux:heading>Tidak ada catatan emosi</flux:heading>
                        </td>
                    </tr>
                @endforelse
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
            datasets: @json($datasets),
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: @json($chartTitle),
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
                            color: isDark ? '#ffffff' : '#000000',
                            stepSize: 1,
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
