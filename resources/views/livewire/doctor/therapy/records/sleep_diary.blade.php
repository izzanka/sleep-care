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

        $labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5', 'Minggu 6'];

        return [
            'sleepDiaries' => $sleepDiaries,
            'questions' => $questions,
            'labels' => $labels,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Sleep Diary'])
{{--    <x-therapies.on-going-layout>--}}
        <div x-data="{ openIndex: null }">
            <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
                <div class="relative w-full">
                    <canvas id="lineChart" class="w-full h-full mb-5"></canvas>
                    <flux:separator class="mt-4 mb-4"></flux:separator>
                    <canvas id="barChart" class="w-full h-full mt-5"></canvas>
                </div>
            </div>

            <flux:separator class="mt-4 mb-4"></flux:separator>
            @foreach($sleepDiaries as $index => $sleepDiary)
                <div
                    class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 mb-5 dark:border-transparent"
                    x-ref="card{{ $index }}"
                >
                    <div class="flex items-center w-full">
                        <flux:icon.calendar class="mr-2" />
                        <flux:button
                            variant="ghost"
                            class="w-full"
                            @click="
                        if (openIndex === {{ $index }}) {
                            openIndex = null
                        } else {
                            openIndex = {{ $index }},
                            $nextTick(() => {
                                const card = $refs['card{{ $index }}']
                                const offset = 20
                                const top = card.getBoundingClientRect().top + window.scrollY - offset
                                window.scrollTo({ top, behavior: 'smooth' })
                            })
                        }
                    "
                        >
                            <div class="flex items-center justify-between w-full">
                                Sleep Diary Minggu Ke-{{ $index }}
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="w-4 h-4 transition-transform duration-300"
                                    :class="openIndex === {{ $index }} ? 'rotate-180' : ''"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </div>
                        </flux:button>
                    </div>

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
                                    <td class="p-2 text-center font-bold" colspan="8">Siang Hari</td>
                                </tr>
                                @foreach($questions as $question)
                                    <tr>
                                        <td class="border p-2 text-center">{{ $question->question }}</td>
                                        @foreach($sleepDiary as $diary)
                                            @php
                                                $entry = $diary->questionAnswers->firstWhere('question_id', $question->id);
                                            @endphp
                                            <td class="border p-2">
                                                <div class="flex justify-center items-center h-full">
                                                    @if($entry->answer->type == QuestionType::BINARY->value)
                                                        @if($entry->answer->answer)
                                                            <flux:icon.check-circle class="text-green-500" />
                                                        @else
                                                            <flux:icon.x-circle class="text-red-500" />
                                                        @endif
                                                    @else
                                                        {{ $entry->answer->answer }}
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
        </div>
{{--    </x-therapies.on-going-layout>--}}
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
                    data: [30, 20, 36, 40, 44, 45],
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 10
                },
                {
                    label: 'Total Terbangun di Malam Hari',
                    data: [14, 28, 7, 5, 2, 1],
                    fill: false,
                    pointRadius: 5,
                    pointHoverRadius: 10
                },
                {
                    label: 'Total Skala Kualitas Tidur',
                    data: [20, 15, 28, 30, 32, 35],
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
                    data: [7, 6, 5, 4, 3, 2],
                },
                {
                    label: 'Alkohol',
                    data: [0, 0, 0, 0, 0, 0],
                },
                {
                    label: 'Nikotin',
                    data: [1, 0, 0, 0, 0, 0],
                },
                {
                    label: 'Makanan',
                    data: [7, 7, 7, 7, 3, 1],
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
                        max: 50,
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
                        text: 'Total Konsumsi Setelah Pukul 18:00',
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
                        max: 25,
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
