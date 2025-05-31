<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\EmotionRecordQuestionAnswer;
use App\Service\ChartService;
use App\Service\QuestionService;
use App\Service\RecordService;
use App\Service\TherapyService;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    protected ChartService $chartService;
    protected TherapyService $therapyService;
    protected RecordService $recordService;
    protected QuestionService $questionService;

    public $therapy;
    public $emotionRecord;
    public $dropdownLabels;
    public $labels;
    public $chartTitle;
    public $selectedWeek;
    public int $id;
    public int $no;
    public ?string $comment;

    public function boot(ChartService    $chartService,
                         TherapyService  $therapyService,
                         RecordService   $recordService,
                         QuestionService $questionService)
    {
        $this->chartService = $chartService;
        $this->recordService = $recordService;
        $this->therapyService = $therapyService;
        $this->questionService = $questionService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if (!$this->therapy) {
            return $this->redirectRoute('doctor.therapies.in_progress.index');
        }
        $this->emotionRecord = $this->recordService->getEmotionRecord($this->therapy->id);
        $this->labels = $this->chartService->labels;
        $this->dropdownLabels = $this->chartService->labeling($this->therapy->start_date);
        $this->selectedWeek = min((int)$this->therapy->start_date->diffInWeeks(now()) + 1, 6);
        $this->chartTitle = 'Total Frekuensi Kemunculan Emosi';
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

    public function createComment(int $id, int $no)
    {
        $questionAnswer = EmotionRecordQuestionAnswer::find($id);
        if(!$questionAnswer){
            session()->flash('status', ['message' => 'Catatan tidak ditemukan.', 'success' => false]);
        }

        $this->id = $id;
        $this->no = $no;
        $this->comment = $questionAnswer->comment;

        $this->modal('addComment')->show();
    }

    public function storeComment()
    {
        $validated = $this->validate([
            'comment' => ['nullable', 'string','max:225'],
        ]);

        $questionAnswer = EmotionRecordQuestionAnswer::find($this->id);
        if(!$questionAnswer){
            session()->flash('status', ['message' => 'Catatan tidak ditemukan.', 'success' => false]);
        }

        $questionAnswer->update([
            'comment' => $validated['comment'],
        ]);

        session()->flash('status', ['message' => 'Komentar berhasil disimpan.', 'success' => true]);
        $this->reset('comment','id');
        $this->modal('addComment')->close();
        $this->js('window.scrollTo({ top: 240, behavior: "smooth" });');
    }

    public function deleteComment(int $id)
    {
        $questionAnswer = EmotionRecordQuestionAnswer::find($id);
        if(!$questionAnswer){
            session()->flash('status', ['message' => 'Catatan tidak ditemukan.', 'success' => false]);
        }

        $questionAnswer->update([
            'comment' => null,
        ]);
        session()->flash('status', ['message' => 'Berhasil menghapus komentar.', 'success' => true]);
        $this->js('window.scrollTo({ top: 240, behavior: "smooth" });');
    }

    public function with()
    {
        $questions = $this->questionService->get('emotion_record')->pluck('question')->toArray();
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
        $flattened = $emotionFrequencies->flatten()->toArray();
        $maxValue = empty($flattened) ? 0 : $this->chartService->calculateMaxValue($flattened);

        EmotionRecordQuestionAnswer::where('emotion_record_id', $this->emotionRecord->id)->whereNull('is_read')->update(['is_read' => true]);

        return [
            'questions' => $questions,
            'answerRows' => $filteredRows,
            'datasets' => $chartDatasets,
            'maxValue' => $maxValue,
        ];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => null])

    @if($therapy->status === TherapyStatus::IN_PROGRESS)
        <flux:callout icon="information-circle" class="mb-4" color="blue"
                      x-data="{ visible: localStorage.getItem('hideMessageEmotion') !== 'true' }"
                      x-show="visible"
                      x-init="$watch('visible', value => !value && localStorage.setItem('hideMessageEmotion', 'true'))">
            <flux:callout.heading>Catatan Emosi (Emotion Record)</flux:callout.heading>

            <flux:callout.text>
                Digunakan untuk mencatat dan memantau emosi yang dialami pasien, membantu mengenali pola emosional, serta mendukung pengelolaan emosi yang lebih sehat dalam proses terapi.
                <br><br>
                <flux:callout.link href="#" @click="visible = false">Jangan tampilkan lagi.</flux:callout.link>
            </flux:callout.text>
        </flux:callout>
    @endif

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

        <flux:modal name="addComment" class="w-full max-w-md md:max-w-lg lg:max-w-xl p-4 md:p-6">
            <div class="space-y-6">
                <form wire:submit="storeComment">
                    <div>
                        <flux:heading size="lg">Tambah Komentar Untuk Catatan No {{$no}}</flux:heading>
                    </div>
                    <div class="mb-4 mt-4">
                        <flux:textarea rows="2" label="Komentar" wire:model="comment" placeholder="Tambahkan sebuah komentar"/>
                    </div>
                    <flux:button type="submit" variant="primary" class="w-full">Simpan</flux:button>
                </form>
            </div>
        </flux:modal>

        <flux:select wire:model.live="selectedWeek" label="Pilih Minggu" class="flex items-center justify-end mb-4">
            @foreach ($dropdownLabels as $index => $label)
                <flux:select.option :value="$index + 1">{{$label}}</flux:select.option>
            @endforeach
        </flux:select>
        {{--            <label for="week-select" class="mr-2 font-medium">Pilih Minggu:</label>--}}
        {{--            <select id="week-select" wire:model.live="selectedWeek" class="border rounded p-2 dark:bg-zinc-700 dark:text-white">--}}
        {{--                @for ($i = 1; $i <= 6; $i++)--}}
        {{--                    <option value="{{ $i }}">Minggu {{ $i }}</option>--}}
        {{--                @endfor--}}
        {{--            </select>--}}

        <div class="overflow-x-auto mt-4">
            <table class="min-w-[800px] w-full text-sm text-left rounded-lg border overflow-hidden">
                <thead class="bg-blue-400 dark:bg-blue-600 text-white">
                <tr class="text-left">
                    @if($therapy->status === TherapyStatus::IN_PROGRESS)
                    <th class="px-4 py-2 font-medium">Aksi Komentar</th>
                    @endif
                    <th class="px-4 py-2 font-medium">No</th>
                    @foreach($questions as $question)
                        <th class="px-4 py-2 font-medium">{{ $question }}</th>
                    @endforeach
                    <th class="px-4 py-2 font-medium">Komentar</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($answerRows as $index => $row)
                    <tr class="text-left">
                        @if($therapy->status === TherapyStatus::IN_PROGRESS)
                            <td class="px-4 py-2 text-center">
                                @php
                                    $firstAnswer = $row->first(); // Get first item in pivot row
                                    $pivotId = $firstAnswer?->id;
                                    $comment = $firstAnswer?->comment;
                                @endphp

                                @if ($pivotId)
                                    <div class="flex items-center space-x-1 justify-center">
                                        @if ($comment)
                                            {{-- Edit and Delete buttons if comment exists --}}
                                            <flux:button
                                                variant="primary"
                                                size="xs"
                                                icon="pencil-square"
                                                wire:click="createComment({{ $pivotId }}, {{ $loop->iteration }})"
                                            />
                                            <flux:button
                                                variant="danger"
                                                size="xs"
                                                icon="trash"
                                                wire:confirm="Apa anda yakin ingin menghapus komentar ini?"
                                                wire:click="deleteComment({{ $pivotId }})"
                                            />
                                        @else
                                            {{-- Plus button if no comment --}}
                                            <flux:button
                                                variant="primary"
                                                size="xs"
                                                icon="plus"
                                                wire:click="createComment({{ $pivotId }}, {{ $loop->iteration }})"
                                            />
                                        @endif
                                    </div>
                                @endif
                            </td>
                        @endif
                        <td class="px-4 py-2 text-center">{{ $index + 1 }}</td>
                        @foreach($questions as $question)
                            @php
                                $answerData = $row->firstWhere('question.question', $question)?->answer;
                                $type = $answerData?->type ?? null;
                                $value = $answerData?->answer ?? null;

                                $formattedValue = match($type) {
                                    QuestionType::DATE->value => $value ? Carbon::parse($value)->isoFormat('D MMMM') : '-',
                                    QuestionType::TIME->value, QuestionType::NUMBER->value => $value ?? '-',
                                    default => $value ?? '-',
                                };

                                $alignment = in_array($type, [QuestionType::DATE->value, QuestionType::TIME->value, QuestionType::NUMBER->value]) ? 'text-center' : 'text-left';
                            @endphp
                            <td class="px-4 py-2 {{ $alignment }}">
                                {{ $formattedValue }}
                            </td>
                        @endforeach
                        <td class="px-4 py-2">
                            {{ $comment ?? '-'}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-2 text-center" colspan="11">
                            <flux:heading class="mt-2">Belum ada catatan emosi</flux:heading>
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
    const canvas = document.getElementById('emotionRecordChart');
    const ctx = canvas?.getContext('2d');
    const isDark = document.documentElement.classList.contains('dark');

    if (ctx) {
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
                            stepSize: 1,
                            color: isDark ? '#ffffff' : '#000000',
                        }
                    }
                }
            }
        };

        new Chart(ctx, config);
    }
</script>
@endscript
