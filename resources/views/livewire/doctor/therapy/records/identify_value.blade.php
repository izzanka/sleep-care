<?php

use App\Enum\ModelFilter;
use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\IdentifyValue;
use App\Models\Therapy;
use App\Service\Records\IdentifyValueService;
use App\Service\TherapyService;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;
    protected IdentifyValueService $identifyValueService;

    public $currentTherapy;
    public $identifyValue;
    public $labels;

    public function boot(TherapyService       $therapyService,
                         IdentifyValueService $identifyValueService)
    {
        $this->identifyValueService = $identifyValueService;
        $this->therapyService = $therapyService;
    }

    public function mount()
    {
        $doctorId = auth()->user()->doctor->id;
        $this->currentTherapy = $this->therapyService->getCurrentTherapy($doctorId);
        if (!$this->currentTherapy) {
            $this->redirectRoute('doctor.therapies.in_progress.index');
        }
        $this->identifyValue = $this->getIdentifyValue($this->currentTherapy->id);
        $this->labels = $this->getUniqueNotes();
    }

    public function getIdentifyValue(int $therapyId)
    {
        $filters[] = [
            'operation' => ModelFilter::EQUAL,
            'column' => 'therapy_id',
            'value' => $therapyId,
        ];

        return $this->identifyValueService->get($filters)[0] ?? null;
    }

    protected function getDatasetLabels()
    {
        return $this->identifyValue->questionAnswers
            ->pluck('question.question')
            ->map(fn($q) => explode(',', $q)[0])
            ->unique()
            ->values()
            ->toArray();
    }

    protected function getUniqueNotes()
    {
        return $this->identifyValue->questionAnswers
            ->pluck('answer.note')
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
        <div class="relative w-full max-w-md mx-auto">
            <canvas id="identifyValueChart" class="w-full h-full"></canvas>
        </div>

        <flux:separator class="mt-4 mb-4"/>

        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm border mb-2 mt-2">
                <thead>
                <tr class="text-center">
                    <th class="border p-2">No</th>
                    <th class="border p-2">Area</th>
                    <th class="border p-2">{{ $datasetLabels[1] ?? '-' }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($labels as $index => $label)
                    <tr class="text-center">
                        <td class="border p-2">{{ $loop->iteration }}</td>
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
