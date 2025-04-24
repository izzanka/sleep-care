<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\SleepDiary;
use App\Models\Therapy;
use App\Service\ChartService;
use App\Service\Records\SleepDiaryService;
use App\Service\TherapyService;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    protected ChartService $chartService;
    protected SleepDiaryService $sleepDiaryService;
    protected TherapyService $therapyService;

    public $therapy;

    public function boot(ChartService $chartService,
                         SleepDiaryService $sleepDiaryService,
                         TherapyService $therapyService)
    {
        $this->chartService = $chartService;
        $this->sleepDiaryService = $sleepDiaryService;
        $this->therapyService = $therapyService;
    }

    public function mount()
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->getCurrentTherapy($doctorId);
        if (!$this->therapy) {
            $this->redirectRoute('doctor.therapies.in_progress.index');
        }
    }

    public function getSleepDiaries()
    {
        return SleepDiary::where('therapy_id', $this->therapy->id)
            ->orderBy('week')
            ->orderBy('day')
            ->get()
            ->groupBy('week');
    }

    public function getQuestions($sleepDiaries)
    {
        $questions = $sleepDiaries
            ->flatten(1)
            ->pluck('questionAnswers')
            ->flatten()
            ->pluck('question')
            ->unique('id')
            ->values();

        return $questions
            ->filter(fn($q) => is_null($q->parent_id))
            ->map(function ($parent) use ($questions) {
                $parent->children = $questions->where('parent_id', $parent->id)->values();
                return $parent;
            })
            ->values();
    }

    public function with()
    {
        $totalSleepHours = [];
        $totalSleep = [];
        $totalAwakenings = [];
        $totalSleepQuality = [];
        $caffeine = [];
        $alcohol = [];
        $nicotine = [];
        $food = [];

        $questions = [
            16 => &$totalSleepHours,
            3 => &$totalSleep,
            17 => &$totalAwakenings,
            18 => &$totalSleepQuality,
            10 => &$caffeine,
            11 => &$alcohol,
            12 => &$nicotine,
            13 => &$food,
        ];

        $sleepDiaries = $this->getSleepDiaries();

        foreach ($sleepDiaries as $entries) {
            foreach ($questions as $questionId => &$targetArray) {
                $targetArray[] = $entries->sum(function ($diary) use ($questionId) {
                    return (int) $diary->questionAnswers->firstWhere('question_id', $questionId)?->answer?->answer ?? 0;
                });
            }
        }

        $structuredQuestions = $this->getQuestions($sleepDiaries);
        $labels = $this->chartService->labels;

        return [
            'sleepDiaries' => $sleepDiaries,
            'structuredQuestions' => $structuredQuestions,
            'labels' => $labels,
            'dataSleepHours' => $totalSleepHours,
            'dataTotalSleep' => $totalSleep,
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
        <div x-data="{ activeSlide: 0 }" class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
            <div class="relative w-full overflow-hidden">
                <!-- Slides -->
                <div class="flex transition-transform duration-500 ease-in-out" :style="`transform: translateX(-${activeSlide * 100}%);`">
                    <!-- Line Chart Slide -->
                    <div class="w-full flex-shrink-0">
                        <canvas id="lineChart" class="w-full h-80 mb-5"></canvas>
                    </div>
                    <!-- Bar Chart Slide -->
                    <div class="w-full flex-shrink-0">
                        <canvas id="barChart" class="w-full h-80 mb-5"></canvas>
                    </div>
                </div>

                <!-- Controls -->
                <button @click="activeSlide = (activeSlide === 0 ? 1 : 0)"
                        class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-zinc-800 dark:bg-zinc-600 text-white px-3 py-1 rounded-full shadow hover:bg-zinc-700 dark:hover:bg-zinc-500">
                    <flux:icon.chevron-left class="size-4"></flux:icon.chevron-left>
                </button>
                <button @click="activeSlide = (activeSlide === 1 ? 0 : 1)"
                        class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-zinc-800 dark:bg-zinc-600 text-white px-3 py-1 rounded-full shadow hover:bg-zinc-700 dark:hover:bg-zinc-500">
                    <flux:icon.chevron-right class="size-4"></flux:icon.chevron-right>
                </button>

                <!-- Indicators -->
                <div class="flex justify-center space-x-2 mt-4">
                    <template x-for="index in 2" :key="index">
                        <button @click="activeSlide = index - 1"
                                :class="{
                            'bg-blue-600 dark:bg-blue-400': activeSlide === index - 1,
                            'bg-zinc-400 dark:bg-zinc-500': activeSlide !== index - 1
                        }"
                                class="w-3 h-3 rounded-full transition-colors duration-300"></button>
                    </template>
                </div>
            </div>
        </div>
        {{--        <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">--}}
{{--            <div class="relative w-full">--}}
{{--                <canvas id="lineChart" class="w-full h-full mb-5"></canvas>--}}
{{--                <flux:separator class="mt-4 mb-4"/>--}}
{{--                <canvas id="barChart" class="w-full h-full mt-5"></canvas>--}}
{{--            </div>--}}
{{--        </div>--}}

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
                                @foreach($sleepDiary as $diary)
                                    @php
                                        Carbon::setLocale('id');
                                    @endphp
                                    <th class="border p-2 text-center">{{$diary->date->translatedFormat('l')}}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <th class="border p-2 text-center">Tanggal</th>
                                @foreach($sleepDiary as $diary)
                                    <th class="border p-2 text-center">{{ $diary->date->format('d/m') }}</th>
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
                    label: 'Total Jam Tidur Siang',
                    data: @json($dataTotalSleep),
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 10
                },
                {
                    label: 'Total Jam Tidur Malam',
                    data: @json($dataSleepHours),
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 10
                },
                {
                    label: 'Total Bangun Malam',
                    data: @json($dataAwakenings),
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 10
                },
                {
                    label: 'Total Kualitas Tidur',
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
                        max: 60,
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
