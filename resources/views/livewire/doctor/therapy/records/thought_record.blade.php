<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\ThoughtRecordQuestionAnswer;
use App\Service\ChartService;
use App\Service\QuestionService;
use App\Service\RecordService;
use App\Service\TherapyService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component {

    protected ChartService $chartService;
    protected TherapyService $therapyService;
    protected RecordService $recordService;
    protected QuestionService $questionService;

    public $text;
    public $therapy;
    public $labels;
    public $selectedWeek;
    public $thoughtRecord;
    public $dropdownLabels;
    public int $id;
    public int $no;
    public ?string $comment;

    public function boot(ChartService    $chartService,
                         TherapyService  $therapyService,
                         RecordService   $recordService,
                         QuestionService $questionService)
    {
        $this->chartService = $chartService;
        $this->therapyService = $therapyService;
        $this->recordService = $recordService;
        $this->questionService = $questionService;
    }

    public function mount(int $therapyId)
    {
        $doctorId = auth()->user()->doctor->id;
        $this->therapy = $this->therapyService->get(doctorId: $doctorId, id: $therapyId)->first();
        if (!$this->therapy) {
            return $this->redirectRoute('doctor.therapies.in_progress.index');
        }
        $this->thoughtRecord = $this->recordService->getThoughtRecord($this->therapy->id);
        $this->labels = $this->chartService->labels;
        $this->selectedWeek = min((int)$this->therapy->start_date->diffInWeeks(now()) + 1, 6);
        $this->dropdownLabels = $this->chartService->labeling($this->therapy->start_date);
        $this->text = 'Total Frekuensi Kemunculan Pikiran';
    }

    private function extractQuestions()
    {
        return $this->questionService->get('thought_record')->pluck('question')->toArray();
    }

    private function countThoughtRecordDates($chunks)
    {
        return $chunks->map(function ($chunk) {
            return $chunk->keyBy(fn($qa) => $qa->question_id)[23]->answer->answer;
        })->filter()->countBy();
    }

    private function groupCountsByWeek($counts)
    {
        $startDate = $this->therapy->start_date;

        $weeks = collect(range(1, 6))->mapWithKeys(fn($week) => ["Minggu $week" => 0]);

        $counts->each(function ($count, $date) use ($startDate, &$weeks) {
            $dayDiff = $startDate->diffInDays($date, false);
            if ($dayDiff >= 0) {
                $week = intdiv($dayDiff, 7) + 1;
                if ($week >= 1 && $week <= 6) {
                    $weeks["Minggu $week"] += $count;
                }
            }
        });

        return $weeks;
    }

    public function createComment(int $id, int $no)
    {
        $questionAnswer = ThoughtRecordQuestionAnswer::find($id);
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

        $questionAnswer = ThoughtRecordQuestionAnswer::find($this->id);
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
        $questionAnswer = ThoughtRecordQuestionAnswer::find($id);
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
        $questions = $this->extractQuestions();
        $chunks = $this->thoughtRecord->questionAnswers->chunk(count($questions))->sortByDesc(function ($chunk) {
            $dateAnswer = optional($chunk->keyBy(fn($qa) => $qa->question_id)[23]->answer)->answer;
            return $dateAnswer ? Carbon::parse($dateAnswer) : null;
        })->values();
        $filteredRows = $chunks->filter(function ($chunk) {
            $groupedAnswers = $chunk->keyBy(fn($qa) => $qa->question_id);
            $date = Carbon::parse($groupedAnswers[23]->answer->answer);
            $weekNumber = (int)$this->therapy->start_date->diffInWeeks($date) + 1;
            return min($weekNumber, 6) == $this->selectedWeek;
        })->values();

        $dateCounts = $this->countThoughtRecordDates($chunks);
        $weeklyData = $this->groupCountsByWeek($dateCounts);
        $weeklySum = array_sum($weeklyData->toArray()) === 0;
        $maxValue = $weeklySum ? 0 : $this->chartService->calculateMaxValue($weeklyData->toArray());

        ThoughtRecordQuestionAnswer::where('thought_record_id', $this->thoughtRecord->id)->whereNull('is_read')->update(['is_read' => true]);

        return [
            'thoughtRecordQuestions' => $questions,
            'chunks' => $filteredRows,
            'data' => $weeklyData->values()->toArray(),
            'maxValue' => $maxValue,
        ];
    }

}; ?>

<section>
    @include('partials.main-heading', ['title' => null])

    @if($therapy->status === TherapyStatus::IN_PROGRESS)
    <flux:callout icon="information-circle" class="mb-4" color="blue"
                  x-data="{ visible: localStorage.getItem('hideMessageThought') !== 'true' }"
                  x-show="visible"
                  x-init="$watch('visible', value => !value && localStorage.setItem('hideMessageThought', 'true'))">
        <flux:callout.heading>Catatan Pikiran (Thought Record)</flux:callout.heading>

        <flux:callout.text>
            Digunakan untuk mencatat dan memantau pola pikir pasien, membantu mengenali pikiran negatif, serta untuk mengubah cara pandang yang lebih adaptif dan sehat.
            <br><br>
            <flux:callout.link href="#" @click="visible = false">Jangan tampilkan lagi.</flux:callout.link>
        </flux:callout.text>
    </flux:callout>
    @endif

    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="flex">
            <div class="w-full flex-shrink-0">
                <canvas id="thoughtRecordChart" class="w-full h-80 mb-4"></canvas>
            </div>
        </div>

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

        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm mb-2 mt-2 rounded-lg border overflow-hidden">
                <thead class="bg-blue-400 dark:bg-blue-600 text-white">
                <tr class="text-left">
                    @if($therapy->status === TherapyStatus::IN_PROGRESS)
                        <th class="px-4 py-2 font-medium">Aksi Komentar</th>
                    @endif
                    <th class="px-4 py-2 font-medium">No</th>
                    @foreach($thoughtRecordQuestions as $question)
                        <th class="px-4 py-2 font-medium">{{ $question }}</th>
                    @endforeach
                    <th class="px-4 py-2 font-medium">Komentar</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($chunks as $index => $chunk)
                    @php
                        $firstAnswer = $chunk->first(); // first pivot item
                        $pivotId = $firstAnswer?->id;
                        $comment = $firstAnswer?->comment;
                        $hasComment = !empty($comment);
                    @endphp
                    <tr>
                        @if($therapy->status === TherapyStatus::IN_PROGRESS)
                            <td class="px-4 py-2 text-center">
                                @if($hasComment)
                                    <div class="flex space-x-1">
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
                                    </div>
                                @else
                                    <flux:button
                                        variant="primary"
                                        size="xs"
                                        icon="plus"
                                        wire:click="createComment({{ $pivotId }}, {{ $index + 1 }})"
                                    />
                                @endif
                            </td>
                        @endif
                        <td class="px-4 py-2 text-center">{{ $index + 1 }}</td>
                        @foreach($thoughtRecordQuestions as $header)
                            @php
                                $answer = $chunk->firstWhere('question.question', $header)->answer;
                                $value = $answer->answer;
                                $type = $answer->type;
                            @endphp
                            <td class="px-4 py-2 p-2">
                                @if($type === QuestionType::DATE->value)
                                    <div class="text-center">
                                        {{ Carbon::parse($value)->isoFormat('D MMMM') }}
                                    </div>
                                @elseif($type == QuestionType::TIME->value)
                                    <div class="text-center">
                                        {{$value ?? '-'}}
                                    </div>
                                @else
                                    <div class="text-left">
                                        @if(Str::isJson($value) && is_array(json_decode($value, true)))
                                            @foreach(json_decode($value, true) as $txt)
                                                <div class="py-1">
                                                    {{ $txt }}
                                                </div>
                                            @endforeach
                                        @else
                                            {{ $value ?? '-' }}
                                        @endif
                                    </div>
                                @endif
                            </td>
                        @endforeach
                        <td class="px-4 py-2">
                            {{ $comment ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-2 text-center" colspan="7">
                            <flux:heading class="mt-2">Belum ada catatan pikiran</flux:heading>
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
    const canvas = document.getElementById('thoughtRecordChart');
    const ctx = canvas?.getContext('2d');
    const isDark = document.documentElement.classList.contains('dark');

    if (ctx) {
        const data = {
            labels: @json($labels),
            datasets: [
                {
                    label: 'Total',
                    data: @json($data),
                    borderWidth: 1,
                }
            ],
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: @json($text),
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
