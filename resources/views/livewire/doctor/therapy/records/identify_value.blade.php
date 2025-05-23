<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\IdentifyValueQuestionAnswer;
use App\Service\QuestionService;
use App\Service\RecordService;
use App\Service\TherapyService;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;
    protected RecordService $recordService;
    protected QuestionService $questionService;

    public $therapy;
    public $identifyValue;
    public $labels;

    public function boot(TherapyService $therapyService,
                         RecordService  $recordService, QuestionService $questionService)
    {
        $this->recordService = $recordService;
        $this->therapyService = $therapyService;
        $this->questionService = $questionService;
    }

    public function mount()
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->get(doctorId: $doctorId, status: TherapyStatus::IN_PROGRESS->value)->first();
        if (!$this->therapy) {
            return $this->redirectRoute('doctor.therapies.in_progress.index');
        }
        $this->identifyValue = $this->recordService->getIdentifyValue($this->therapy->id);
        $this->labels = $this->getUniqueNotes();
    }

    protected function getDatasetLabels()
    {
        return $this->questionService->get('identify_value')->pluck('question')
            ->map(fn($q) => explode(',', $q)[0])->toArray();
    }

    protected function getUniqueNotes()
    {
        return $this->identifyValue->questionAnswers->pluck('answer.note')
            ->filter()
            ->unique()
            ->values();
    }

    protected function getNumberAnswers()
    {
        return collect($this->identifyValue->questionAnswers)
            ->filter(fn($qa) => $qa->answer->type === QuestionType::NUMBER->value)
            ->groupBy(fn($qa) => explode(',', $qa->question->question)[0])
            ->map(fn($group) => $group->pluck('answer.answer')->map(fn($val) => (int)$val))
            ->toArray();
    }

    protected function getTextAnswers()
    {
        return collect($this->identifyValue->questionAnswers)
            ->filter(fn($qa) => $qa->answer->type === QuestionType::TEXT->value)
            ->groupBy(fn($qa) => explode(',', $qa->question->question)[0])
            ->map(fn($group) => $group->pluck('answer.answer'))
            ->toArray();
    }

    public function with()
    {
        $dataset = $this->getDatasetLabels();
        $numberAnswers = $this->getNumberAnswers();
        $textAnswers = $this->getTextAnswers();

        IdentifyValueQuestionAnswer::where('identify_value_id', $this->identifyValue->id)->whereNull('is_read')->update(['is_read' => true]);

        return [
            'datasetLabels' => $dataset,
            'numberAnswers' => $numberAnswers,
            'textAnswers' => $textAnswers,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Catatan Identifikasi Nilai (Identify Value)'])

    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="flex">
            <div class="w-full max-w-lg flex-shrink-0 mx-auto">
                <canvas id="identifyValueChart" class="w-full h-80 mb-4"></canvas>
            </div>
        </div>
        <flux:separator class="mt-4 mb-4"/>

        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm border mb-2 mt-2">
                <thead>
                <tr class="text-center">
                    <th class="border p-2">No</th>
                    <th class="border p-2">Area</th>
                    <th class="border p-2">{{ $datasetLabels[0] }} (1-10)</th>
                    <th class="border p-2">{{ $datasetLabels[2] }} (1-10)</th>
                    <th class="border p-2">{{ $datasetLabels[1] }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($labels as $index => $label)
                    <tr class="text-left">
                        <td class="border p-2 text-center">{{ $loop->iteration }}</td>
                        <td class="border p-2">{{ $label }}</td>
                        <td class="border p-2 text-center">{{ $numberAnswers['Skala Kepentingan'][$index] }}</td>
                        <td class="border p-2 text-center">{{ $numberAnswers['Skor Kesesuaian'][$index] }}</td>
                        <td class="border p-2">
                            {{ $textAnswers[$datasetLabels[1]][$index] ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="border p-4 text-center" colspan="5">
                            <flux:heading>Belum ada catatan</flux:heading>
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
        const canvas = document.getElementById('identifyValueChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');

        const data = {
            labels: @json($labels),
            datasets: [
                {
                    label: @json($datasetLabels[0]),
                    data: @json($numberAnswers[$datasetLabels[0]] ?? []),
                    fill: true,
                },
                {
                    label: @json($datasetLabels[2]),
                    data: @json($numberAnswers[$datasetLabels[2]] ?? []),
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
                        text: 'Perbandingan Kepentingan dan Kesesuaian',
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
                            backdropColor: 'transparent',
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
