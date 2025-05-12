<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Service\QuestionService;
use App\Service\RecordService;
use App\Service\TherapyService;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;
    protected RecordService $recordService;
    protected QuestionService $questionService;

    public $therapy;
    public $committedAction;

    public function boot(TherapyService $therapyService, RecordService $recordService, QuestionService $questionService)
    {
        $this->therapyService = $therapyService;
        $this->recordService = $recordService;
        $this->questionService = $questionService;
    }

    public function mount()
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->get(doctorId: $doctorId, status: TherapyStatus::IN_PROGRESS->value)->first();
        if (!$this->therapy) {
            return $this->redirectRoute('doctor.therapies.in_progress.index');
        }
        $this->committedAction = $this->recordService->getCommittedActions($this->therapy->id);
    }

    public function prepareChartData()
    {
        $questionAnswers = $this->committedAction->questionAnswers;
        $executionAnswers = $questionAnswers->filter(fn($qa) => $qa->question_id === 39);

        return [
            'labels' => ['Terlaksana', 'Tidak Terlaksana'],
            'title' => 'Status Tindakan',
            'data' => [
                $executionAnswers->where('answer.answer', 1)->count(),
                $executionAnswers->where('answer.answer', 0)->count(),
            ],
        ];
    }

    public function with()
    {
        $questionAnswers = $this->committedAction->questionAnswers;
        $questionLabels = $this->questionService->get('committed_action')->pluck('question');
        $tableRows = $questionAnswers->sortByDesc('answer.created_at')->chunk($questionLabels->count());
        $chart = $this->prepareChartData();

        return [
            'questions' => $questionLabels,
            'rows' => $tableRows,
            ...$chart,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Catatan Tindakan Berkomitmen (Committed Action)'])

    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="flex">
            <div class="w-full max-w-md flex-shrink-0 mx-auto">
                <canvas id="committedActionChart" class="w-full h-80 mb-4"></canvas>
            </div>
        </div>

        {{--        <div class="relative w-full max-w-md mx-auto">--}}
        {{--            <canvas id="committedActionChart" class="w-full h-full"></canvas>--}}
        {{--        </div>--}}

        <flux:separator class="mt-4 mb-4"></flux:separator>

        <div class="overflow-x-auto border mt-4">
            <table class="min-w-[800px] table-auto w-full text-sm text-left">
                <thead>
                <tr>
                    <th class="border p-3 text-center">No</th>
                    @foreach($questions as $question)
                        <th class="border p-3 text-center whitespace-nowrap">{{ $question }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $index => $row)
                    <tr>
                        <td class="border p-3 text-center">{{ $index + 1 }}</td>
                        @foreach($questions as $question)
                            @php
                                $answerData = $row->firstWhere('question.question', $question)?->answer;
                                $isBinary = $answerData?->type === QuestionType::BINARY->value;
                                $value = $answerData?->answer ?? null;
                            @endphp
                            <td class="border p-3">
                                @if($isBinary)
                                    <div class="flex justify-center items-center h-full">
                                        @if($value)
                                            <flux:icon.check-circle class="text-green-500 w-5 h-5"/>
                                        @else
                                            <flux:icon.x-circle class="text-red-500 w-5 h-5"/>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-left">
                                        {{ $value ?? '-' }}
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="border p-4 text-center" colspan="{{ count($questions) + 1 }}">
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
        const canvas = document.getElementById('committedActionChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');

        const config = {
            type: 'doughnut',
            data: {
                labels: @json($labels),
                datasets: [{
                    data: @json($data),
                    borderWidth: 0.5,
                }]
            },
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
                }
            }
        };

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, config);
    }

    document.addEventListener('DOMContentLoaded', createChart);

    const observer = new MutationObserver(createChart);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
</script>
@endscript

