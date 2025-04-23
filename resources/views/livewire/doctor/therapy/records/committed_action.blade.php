<?php

use App\Enum\ModelFilter;
use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Service\Records\CommittedActionService;
use App\Service\TherapyService;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;
    protected CommittedActionService $committedActionService;

    public $currentTherapy;
    public $committedAction;

    public function boot(TherapyService $therapyService, CommittedActionService $committedActionService)
    {
        $this->therapyService = $therapyService;
        $this->committedActionService = $committedActionService;
    }

    public function mount()
    {
        $doctorId = auth()->user()->doctor->id;

        $this->currentTherapy = $this->getCurrentTherapy($doctorId);
        if (!$this->currentTherapy) {
            $this->redirectRoute('doctor.therapies.in_progress.index');
        }

        $this->committedAction = $this->getCommittedAction($this->currentTherapy->id);
        if (!$this->committedAction) {
            $this->redirectRoute('doctor.therapies.in_progress.index');
        }
    }

    public function getCurrentTherapy(int $doctorId)
    {
        $filters = [
            [
                'operation' => ModelFilter::EQUAL,
                'column' => 'doctor_id',
                'value' => $doctorId,
            ],
            [
                'operation' => ModelFilter::EQUAL,
                'column' => 'status',
                'value' => TherapyStatus::IN_PROGRESS->value,
            ],
        ];

        return $this->therapyService->get($filters)[0] ?? null;
    }

    public function getCommittedAction(int $therapyId)
    {
        $filters = [
            [
                'operation' => ModelFilter::EQUAL,
                'column' => 'therapy_id',
                'value' => $therapyId,
            ],
        ];

        return $this->committedActionService->get($filters)[0] ?? null;
    }

    public function prepareChartData()
    {
        $questionAnswers = $this->committedAction->questionAnswers;
        $executionAnswers = $questionAnswers->filter(fn($qa) => $qa->question?->question === 'Terlaksana');

        return [
            'labels' => ['Terlaksana', 'Tidak Terlaksana'],
            'data' => [
                $executionAnswers->where('answer.answer', 1)->count(),
                $executionAnswers->where('answer.answer', 0)->count(),
            ],
            'title' => 'Statistik',
        ];
    }


    public function with()
    {
        $questionAnswers = $this->committedAction->questionAnswers;
        $questionLabels = $questionAnswers->pluck('question.question')->unique()->values();
        $tableRows = $questionAnswers->chunk($questionLabels->count());
        $chart = $this->prepareChartData();

        return [
            'therapy' => $this->currentTherapy,
            'committedAction' => $this->committedAction,
            'questions' => $questionLabels,
            'rows' => $tableRows,
            ...$chart,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Committed Action'])

    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="relative w-full max-w-md mx-auto">
            <canvas id="committedActionChart" class="w-full h-full"></canvas>
        </div>

        <flux:separator class="mt-4 mb-4"></flux:separator>

        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm border mb-2 mt-2">
                <thead>
                <tr>
                    <th class="border p-2 text-center">No</th>
                    @foreach($questions as $question)
                        <th class="border p-2 text-center">{{ $question }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $index => $row)
                    <tr>
                        <td class="border p-2 text-center">{{ $index + 1 }}</td>
                        @foreach($questions as $question)
                            @php
                                $answer = $row->firstWhere('question.question', $question);
                            @endphp
                            <td class="border p-2 text-center">
                                @if($answer?->answer->type === QuestionType::BINARY->value)
                                    <div class="flex justify-center items-center h-full">
                                        @if($answer->answer->answer)
                                            <flux:icon.check-circle class="text-green-500"/>
                                        @else
                                            <flux:icon.x-circle class="text-red-500"/>
                                        @endif
                                    </div>
                                @else
                                    {{ $answer->answer->answer ?? '-' }}
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
        const canvas = document.getElementById('committedActionChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');

        const data = {
            labels: @json($labels),
            datasets: [{
                data: @json($data),
                borderWidth: 0.5,
            }]
        };

        const config = {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: @json($title),
                        color: isDark ? '#ffffff' : '#000000',
                    },
                    legend: {
                        labels: {
                            color: isDark ? '#ffffff' : '#000000',
                        }
                    }
                },
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
