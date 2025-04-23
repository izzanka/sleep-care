<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\SleepDiary;
use App\Models\Therapy;
use App\Service\ChartService;
use Livewire\Volt\Component;

new class extends Component {
    protected ChartService $chartService;

    public function boot(ChartService $chartService)
    {
        $this->chartService = $chartService;
    }

    public function with()
    {
        $doctor = auth()->user()->doctor;
        $therapy = Therapy::where('doctor_id', $doctor->id)
            ->where('status', TherapyStatus::IN_PROGRESS->value)
            ->first();

        $sleepDiaries = SleepDiary::where('therapy_id', $therapy->id)
            ->orderBy('week')
            ->orderBy('day')
            ->get()
            ->groupBy('week');

        $allQuestions = $sleepDiaries
            ->flatten(1)
            ->pluck('questionAnswers')
            ->flatten()
            ->pluck('question')
            ->unique('id')
            ->values();

        $structuredQuestions = $allQuestions
            ->filter(fn($q) => is_null($q->parent_id))
            ->map(function ($parent) use ($allQuestions) {
                $parent->children = $allQuestions->where('parent_id', $parent->id)->values();
                return $parent;
            })
            ->values();

        $labels = $this->chartService->labels;

        $totalSleepHours = [];
        $totalAwakenings = [];
        $totalSleepQuality = [];

        $caffeine = [];
        $alcohol = [];
        $nicotine = [];
        $food = [];

        foreach ($sleepDiaries as $entries) {

            $totalSleepHours[] = $entries->sum(function ($diary) {
                return (int)$diary->questionAnswers->firstWhere('question_id', 16)?->answer?->answer ?? 0;
            });

            $totalAwakenings[] = $entries->sum(function ($diary) {
                return (int)$diary->questionAnswers->firstWhere('question_id', 17)?->answer?->answer ?? 0;
            });

            $totalSleepQuality[] = $entries->sum(function ($diary) {
                return (int)$diary->questionAnswers->firstWhere('question_id', 18)?->answer?->answer ?? 0;
            });

            $caffeine[] = $entries->sum(function ($diary) {
                return (int)$diary->questionAnswers->firstWhere('question_id', 10)?->answer?->answer ?? 0;
            });

            $alcohol[] = $entries->sum(function ($diary) {
                return (int)$diary->questionAnswers->firstWhere('question_id', 11)?->answer?->answer ?? 0;
            });

            $nicotine[] = $entries->sum(function ($diary) {
                return (int)$diary->questionAnswers->firstWhere('question_id', 12)?->answer?->answer ?? 0;
            });

            $food[] = $entries->sum(function ($diary) {
                return (int)$diary->questionAnswers->firstWhere('question_id', 13)?->answer?->answer ?? 0;
            });
        }

        return [
            'sleepDiaries' => $sleepDiaries,
            'structuredQuestions' => $structuredQuestions,
            'labels' => $labels,
            'dataSleepHours' => $totalSleepHours,
            'dataAwakenings' => $totalAwakenings,
            'dataSleepQuality' => $totalSleepQuality,
            'dataCaffeine' => $caffeine,
            'dataAlcohol' => $alcohol,
            'dataNicotine' => $nicotine,
            'dataFood' => $food,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Sleep Diary'])

    <div x-data="{ openIndex: null }">
        <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
            <div class="relative w-full">
                <canvas id="lineChart" class="w-full h-full mb-5"></canvas>
                <flux:separator class="mt-4 mb-4"/>
                <canvas id="barChart" class="w-full h-full mt-5"></canvas>
            </div>
        </div>

        <flux:separator class="mt-4 mb-4"/>

        @foreach($sleepDiaries as $index => $sleepDiary)
            <div
                class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 mb-5 dark:border-transparent"
                x-ref="card{{ $index }}"
            >
                <div class="flex items-center w-full">
                    <flux:icon.calendar class="mr-2"/>

                    <flux:button
                        variant="ghost"
                        class="w-full"
                        @click="
                            openIndex = (openIndex === {{ $index }}) ? null : {{ $index }};
                            if (openIndex === {{ $index }}) {
                                $nextTick(() => {
                                    const card = $refs['card{{ $index }}'];
                                    const offset = 20;
                                    const top = card.getBoundingClientRect().top + window.scrollY - offset;
                                    window.scrollTo({ top, behavior: 'smooth' });
                                });
                            }
                        "
                    >
                        <div class="flex items-center justify-between w-full">
                            Sleep Diary Minggu Ke-{{ $index }}
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke-width="1.5"
                                 stroke="currentColor"
                                 class="w-4 h-4 transition-transform duration-300"
                                 :class="openIndex === {{ $index }} ? 'rotate-180' : ''">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </div>
                    </flux:button>
                </div>

                {{-- Expandable Table Section --}}
                <div x-show="openIndex === {{ $index }}" x-transition.duration.200ms class="mt-4">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm border mb-2 mt-2">
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
                                <td class="p-2 text-center font-bold" colspan="9">Siang Hari</td>
                            </tr>

                            @foreach($structuredQuestions as $question)
                                <tr>
                                    <td class="border p-2 text-center font-bold">{{ $question->question }}</td>
                                    @foreach($sleepDiary as $diary)
                                        @php
                                            $entry = $diary->questionAnswers->firstWhere('question_id', $question->id);
                                        @endphp
                                        <td class="border p-2">
                                            <div class="flex justify-center items-center h-full">
                                                @if($entry?->answer?->type == \App\Enum\QuestionType::BINARY->value)
                                                    @if($entry->answer->answer)
                                                        <flux:icon.check-circle class="text-green-500"/>
                                                    @else
                                                        <flux:icon.x-circle class="text-red-500"/>
                                                    @endif
                                                @else
                                                    {{ $entry->answer->answer ?? '-' }}
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>

                                @foreach($question->children as $child)
                                    <tr>
                                        <td class="border p-2 text-left text-sm">{{ $child->question }}</td>
                                        @foreach($sleepDiary as $diary)
                                            @php
                                                $entry = $diary->questionAnswers->firstWhere('question_id', $child->id);
                                            @endphp
                                            <td class="border p-2 text-center">
                                                {{ $entry->answer->answer ?? '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach

                                @if($question->id == 13)
                                    <tr>
                                        <td class="p-2 text-center font-bold" colspan="9">Malam Hari</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>


@script
<script>
    let barChartInstance;
    let lineChartInstance;

    function createCharts() {
        const lineChartCanvas = document.getElementById('lineChart');
        const barChartCanvas = document.getElementById('barChart');
        if (!lineChartCanvas || !barChartCanvas) return;

        const lineChartCtx = lineChartCanvas.getContext('2d');
        const barChartCtx = barChartCanvas.getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');

        const dataLineChart = {
            labels: @json($labels),
            datasets: [
                {
                    label: 'Total Jam Tidur',
                    data: @json($dataSleepHours),
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 10
                },
                {
                    label: 'Total Terbangun di Malam Hari',
                    data: @json($dataAwakenings),
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 10
                },
                {
                    label: 'Total Skala Kualitas Tidur',
                    data: @json($dataSleepQuality),
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 10
                },
            ],
        };

        const dataBarChart = {
            labels: @json($labels),
            datasets: [
                {
                    label: 'Kafein',
                    data: @json($dataCaffeine),
                },
                {
                    label: 'Alkohol',
                    data: @json($dataAlcohol),
                },
                {
                    label: 'Nikotin',
                    data: @json($dataNicotine),
                },
                {
                    label: 'Makanan',
                    data: @json($dataFood),
                },
            ]
        };

        const configLineChart = {
            type: 'line',
            data: dataLineChart,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Tren Tidur',
                        color: isDark ? '#ffffff' : '#000000',
                    },
                    legend: {
                        labels: {
                            color: isDark ? '#ffffff' : '#000000',
                        }
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: isDark ? '#ffffff' : '#000000',
                        },
                        grid: {
                            display: true,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        min: 1,
                        max: 60,
                        ticks: {
                            color: isDark ? '#ffffff' : '#000000',
                        },
                        grid: {
                            display: true,
                        },
                    },
                },
            }
        };

        const configBarChart = {
            type: 'bar',
            data: dataBarChart,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Tren Konsumsi Setelah Pukul 18:00',
                        color: isDark ? '#ffffff' : '#000000',
                    },
                    legend: {
                        labels: {
                            color: isDark ? '#ffffff' : '#000000',
                        }
                    },
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: {
                            color: isDark ? '#ffffff' : '#000000',
                        },
                    },
                    y: {
                        stacked: true,
                        min: 0,
                        max: 30,
                        ticks: {
                            color: isDark ? '#ffffff' : '#000000',
                        },
                    }
                }
            }
        };

        if (barChartInstance) barChartInstance.destroy();
        if (lineChartInstance) lineChartInstance.destroy();

        barChartInstance = new Chart(lineChartCtx, configLineChart);
        lineChartInstance = new Chart(barChartCtx, configBarChart);
    }

    document.addEventListener('DOMContentLoaded', () => {
        createCharts();
    });

    const observer = new MutationObserver(() => {
        createCharts();
    });

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
</script>
@endscript
