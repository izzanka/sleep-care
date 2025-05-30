<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\SleepDiaryQuestionAnswer;
use App\Service\ChartService;
use App\Service\RecordService;
use App\Service\TherapyService;
use Livewire\Volt\Component;

new class extends Component {
    protected ChartService $chartService;
    protected RecordService $recordService;
    protected TherapyService $therapyService;

    public $therapy;
    public $labels;
    public $dropdownLabels;

    public function boot(ChartService   $chartService,
                         RecordService  $recordService,
                         TherapyService $therapyService)
    {
        $this->chartService = $chartService;
        $this->recordService = $recordService;
        $this->therapyService = $therapyService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;

        $this->therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if (!$this->therapy) {
            return $this->redirectRoute('doctor.therapies.in_progress.index');
        }
        $this->labels = $this->chartService->labels;
        $this->dropdownLabels = $this->chartService->labeling($this->therapy->start_date);
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
        $questionMapping = [
            16 => 'dataSleepHours',
            3 => 'dataTotalSleep',
            17 => 'dataAwakenings',
            18 => 'dataSleepQuality',
            10 => 'dataCaffeine',
            11 => 'dataAlcohol',
            12 => 'dataNicotine',
            13 => 'dataFood',
        ];

        $resultData = array_fill_keys(array_values($questionMapping), []);
        $sleepDiaries = $this->recordService->getSleepDiaries($this->therapy->id);

        $ids = [];

        foreach ($sleepDiaries as $entries) {
            foreach ($questionMapping as $questionId => $dataKey) {
                $sum = $entries->sum(function ($diary) use ($questionId, &$ids) {
                    $questionAnswers = $diary->questionAnswers->firstWhere('is_read', null);
                    if ($questionAnswers) {
                        $ids[] = $diary->id;
                    }
                    return $diary->questionAnswers->firstWhere('question_id', $questionId)->answer->answer ?? 0;
                });
                $resultData[$dataKey][] = $sum;
            }
        }

        SleepDiaryQuestionAnswer::whereIn('sleep_diary_id', $ids)->whereNull('is_read')->update(['is_read' => true]);

        $structuredQuestions = $this->getQuestions($sleepDiaries);

        return array_merge([
            'sleepDiaries' => $sleepDiaries,
            'structuredQuestions' => $structuredQuestions,
        ], $resultData);
    }


}; ?>

<section>
    @include('partials.main-heading', ['title' => null])

    @if($therapy->status === TherapyStatus::IN_PROGRESS)
        <flux:callout icon="information-circle" class="mb-4" color="blue"
                      x-data="{ visible: localStorage.getItem('hideMessageSleep') !== 'true' }"
                      x-show="visible"
                      x-init="$watch('visible', value => !value && localStorage.setItem('hideMessageSleep', 'true'))">
            <flux:callout.heading>Catatan Tidur (Sleep Diary)</flux:callout.heading>

            <flux:callout.text>
                Digunakan untuk mencatat dan memantau pola tidur pasien secara rutin, serta membantu mengidentifikasi
                hubungan antara kualitas tidur dan kondisi psikologis, untuk mendukung perubahan perilaku ke arah yang
                lebih sehat.
                <br><br>
                <flux:callout.link href="#" @click="visible = false">Jangan tampilkan lagi.</flux:callout.link>
            </flux:callout.text>
        </flux:callout>
    @endif

    <div x-data="{ openIndex: null }">
        <div x-data="{ activeSlide: 0 }"
             class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
            <div class="relative w-full overflow-hidden">
                <div class="flex transition-transform duration-500 ease-in-out"
                     :style="`transform: translateX(-${activeSlide * 100}%);`">
                    <div class="w-full flex-shrink-0">
                        <canvas id="lineChart" class="w-full h-80 mb-4"></canvas>
                    </div>
                    <div class="w-full flex-shrink-0">
                        <canvas id="barChart" class="w-full h-80 mb-4"></canvas>
                    </div>
                </div>
                <button @click="activeSlide = (activeSlide === 0 ? 1 : 0)"
                        class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-blue-500 text-white dark:bg-blue-700 px-3 py-1 rounded-full shadow hover:bg-blue-400 dark:hover:bg-blue-600">
                    <flux:icon.chevron-left class="size-4"></flux:icon.chevron-left>
                </button>
                <button @click="activeSlide = (activeSlide === 1 ? 0 : 1)"
                        class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-blue-500 text-white dark:bg-blue-700 px-3 py-1 rounded-full shadow hover:bg-blue-400 dark:hover:bg-blue-600">
                    <flux:icon.chevron-right class="size-4"></flux:icon.chevron-right>
                </button>


                <div class="flex justify-center space-x-2 mt-4">
                    <template x-for="index in 2" :key="index">
                        <button @click="activeSlide = index - 1"
                                :class="{
                            'bg-blue-500 dark:bg-blue-400': activeSlide === index - 1,
                            'bg-zinc-500 dark:bg-zinc-400': activeSlide !== index - 1
                        }"
                                class="w-3 h-3 rounded-full transition-colors duration-300"></button>
                    </template>
                </div>
            </div>
        </div>

        <flux:separator class="mt-4 mb-4"/>

        @foreach($sleepDiaries as $index => $sleepDiary)
            <div
                    class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 mb-5 dark:border-transparent"
                    x-ref="card{{ $index }}"
            >
                <div class="flex items-center w-full">
                    <flux:icon.calendar/>

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
                            Catatan Tidur {{ $dropdownLabels[$index - 1]}}
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

                <div x-show="openIndex === {{ $index }}" x-transition.duration.200ms class="mt-4">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm mb-2 mt-2 rounded-lg border overflow-hidden">
                            <thead class="bg-blue-400 text-white dark:bg-blue-600">
                            <tr>
                                <th class="p-2 text-center">Hari</th>
                                @foreach($sleepDiary as $diary)
                                    <th class="p-2 text-center">{{$diary->date->translatedFormat('l')}}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody class="divide-y">
                            <tr>
                                <th class="p-2 text-center">Tanggal</th>
                                @foreach($sleepDiary as $diary)
                                    <th class="p-2 text-center">{{ $diary->date->isoFormat('D MMMM') }}</th>
                                @endforeach
                            </tr>

                            @if($structuredQuestions->isEmpty())
                                <tr>
                                    <td class="p-4 text-center" colspan="8">
                                        <flux:heading>Belum ada catatan tidur</flux:heading>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td class="p-2 text-center font-bold" colspan="8">Siang Hari</td>
                                </tr>

                                @foreach($structuredQuestions as $question)
                                    <tr>
                                        <td class="p-2 text-center font-bold">{{ $question->question }}</td>
                                        @foreach($sleepDiary as $diary)
                                            @php
                                                $entry = $diary->questionAnswers->firstWhere('question_id', $question->id);
                                            @endphp
                                            <td class="p-2">
                                                <div class="flex justify-center items-center h-full">
                                                    @if($entry && $entry->answer)
                                                        @if($entry->answer->type == QuestionType::BOOLEAN->value)
                                                            @if($entry->answer->answer)
                                                                <flux:icon.check-circle class="text-green-500"/>
                                                            @else
                                                                <flux:icon.x-circle class="text-red-500"/>
                                                            @endif
                                                        @else
                                                            {{ $entry->answer->answer ?? '-' }}
                                                        @endif
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>

                                    @foreach($question->children as $child)
                                        <tr>
                                            <td class="p-2 text-left text-sm">{{ $child->question }}</td>
                                            @foreach($sleepDiary as $diary)
                                                @php
                                                    $entry = $diary->questionAnswers->firstWhere('question_id', $child->id);
                                                @endphp
                                                <td class="p-2 text-center">
                                                    {{ $entry->answer->answer ?? '-' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach

                                    @if($question->id == 13)
                                        <tr>
                                            <td class="p-2 text-center font-bold" colspan="8">Malam Hari</td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
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
    const canvasLine = document.getElementById('lineChart');
    const canvasBar = document.getElementById('barChart');

    const ctxLine = canvasLine?.getContext('2d');
    const ctxBar = canvasBar?.getContext('2d');

    const isDark = document.documentElement.classList.contains('dark');

    if (ctxLine && ctxBar) {
        const dataLine = {
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

        const configLine = {
            type: 'line',
            data: dataLine,
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
                        beginAtZero: false,
                        min: 0,
                        max: 60,
                        ticks: {
                            color: isDark ? '#ffffff' : '#000000',
                        },
                        grid: {
                            display: true,
                        },
                    },
                }
            }
        };

        const dataBar = {
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

        const configBar = {
            type: 'bar',
            data: dataBar,
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
                        min: 0,
                        max: 30,
                        ticks: {
                            color: isDark ? '#ffffff' : '#000000',
                        },
                    }
                }
            }
        };

        new Chart(ctxLine, configLine);
        new Chart(ctxBar, configBar);
    }
</script>
@endscript
