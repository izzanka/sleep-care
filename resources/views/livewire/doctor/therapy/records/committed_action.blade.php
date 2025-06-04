<?php

use App\Enum\QuestionType;
use App\Enum\TherapyStatus;
use App\Models\CommittedActionQuestionAnswer;
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
    public ?string $comment;
    public int $no;
    public int $id;

    public function boot(TherapyService $therapyService, RecordService $recordService, QuestionService $questionService)
    {
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
        $this->committedAction = $this->recordService->getCommittedAction($this->therapy->id);
    }

    public function prepareChartData()
    {
        $questionAnswers = $this->committedAction->questionAnswers;
        $executionAnswers = $questionAnswers->filter(fn($qa) => $qa->question_id === 39);

        return [
            'labels' => ['Terlaksana', 'Tidak Terlaksana'],
            'title' => 'Status Tindakan',
            'data' => [
                $executionAnswers->where('answer.answer', true)->count(),
                $executionAnswers->where('answer.answer', false)->count(),
            ],
        ];
    }

    public function createComment(int $id, int $no)
    {
        $questionAnswer = CommittedActionQuestionAnswer::find($id);
        if (!$questionAnswer) {
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
            'comment' => ['nullable', 'string', 'max:225'],
        ]);

        $questionAnswer = CommittedActionQuestionAnswer::find($this->id);
        if (!$questionAnswer) {
            session()->flash('status', ['message' => 'Catatan tidak ditemukan.', 'success' => false]);
        }

        $questionAnswer->update([
            'comment' => $validated['comment'],
        ]);

        session()->flash('status', ['message' => 'Komentar berhasil disimpan.', 'success' => true]);
        $this->reset('comment', 'id');
        $this->modal('addComment')->close();
        $this->js('window.scrollTo({ top: 240, behavior: "smooth" });');
    }

    public function deleteComment(int $id)
    {
        $questionAnswer = CommittedActionQuestionAnswer::find($id);
        if (!$questionAnswer) {
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
        $questionAnswers = $this->committedAction->questionAnswers;
        $questionLabels = $this->questionService->get('committed_action')->pluck('question');
        $tableRows = $questionAnswers->sortByDesc('answer.created_at')->chunk($questionLabels->count());
        $chart = $this->prepareChartData();

        CommittedActionQuestionAnswer::where('committed_action_id', $this->committedAction->id)->whereNull('is_read')->update(['is_read' => true]);

        return [
            'questions' => $questionLabels,
            'rows' => $tableRows,
            ...$chart,
        ];
    }
}; ?>

<section class="w-full">
    @include('partials.main-heading', ['title' => null])

    @if($therapy->status === TherapyStatus::IN_PROGRESS)
        <flux:callout icon="information-circle" class="mb-4" color="blue"
                      x-data="{ visible: localStorage.getItem('hideMessageAction') !== 'true' }"
                      x-show="visible"
                      x-init="$watch('visible', value => !value && localStorage.setItem('hideMessageAction', 'true'))">
            <flux:callout.heading>Catatan Tindakan (Committed Action)</flux:callout.heading>
            <flux:callout.text>
                Digunakan untuk mencatat tindakan nyata yang dilakukan pasien sebagai bentuk komitmen terhadap
                nilai-nilai pribadinya.
                <br><br>
                <flux:callout.link href="#" @click="visible = false">Jangan tampilkan lagi.</flux:callout.link>
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="relative rounded-lg px-4 sm:px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <!-- Chart Section -->
        <div class="flex">
            <div class="w-full max-w-md flex-shrink-0 mx-auto">
                <canvas id="committedActionChart" class="w-full h-64 sm:h-80 mb-4"></canvas>
            </div>
        </div>

        <flux:separator class="my-4"></flux:separator>

        <!-- Comment Modal -->
        <flux:modal name="addComment" class="w-full max-w-[95vw] sm:max-w-md md:max-w-lg lg:max-w-xl p-4 md:p-6">
            <div class="space-y-4 sm:space-y-6">
                <form wire:submit="storeComment">
                    <div>
                        <flux:heading size="lg">Tambah Komentar Untuk Catatan No {{$no}}</flux:heading>
                    </div>
                    <div class="mb-4 mt-4">
                        <flux:textarea rows="3" label="Komentar" wire:model="comment"
                                       placeholder="Tambahkan sebuah komentar"/>
                    </div>
                    <flux:button type="submit" variant="primary" class="w-full">Simpan</flux:button>
                </form>
            </div>
        </flux:modal>

        <!-- Table Section -->
        <div class="overflow-x-auto mt-4">
            <div class="min-w-[700px]">
                <table class="w-full text-sm text-left rounded-lg border overflow-hidden">
                    <thead class="bg-blue-400 dark:bg-blue-600 text-white">
                    <tr class="text-left">
                        @if($therapy->status === TherapyStatus::IN_PROGRESS)
                            <th class="px-3 py-2 font-medium">Aksi</th>
                        @endif
                        <th class="px-3 py-2 font-medium">No</th>
                        @foreach($questions as $question)
                            <th class="px-3 py-2 font-medium">{{ $question }}</th>
                        @endforeach
                        <th class="px-3 py-2 font-medium">Komentar</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse($rows as $index => $row)
                        @php
                            $firstAnswerWithComment = $row->first(fn($qa) => !empty($qa->comment));
                        @endphp
                        <tr>
                            @if($therapy->status === TherapyStatus::IN_PROGRESS)
                                <td class="px-3 py-2 text-center">
                                    @if($firstAnswerWithComment)
                                        <div class="flex items-center justify-center space-x-1">
                                            <flux:button variant="primary" size="xs" icon="pencil-square"
                                                         wire:click="createComment({{ $firstAnswerWithComment->id }}, {{ $loop->iteration }})"/>
                                            <flux:button variant="danger" size="xs" icon="trash"
                                                         wire:confirm="Apa anda yakin ingin menghapus komentar ini?"
                                                         wire:click="deleteComment({{ $firstAnswerWithComment->id }})"/>
                                        </div>
                                    @else
                                        @php
                                            $firstAnswer = $row->first();
                                        @endphp
                                        @if($firstAnswer)
                                            <flux:button variant="primary" size="xs" icon="plus"
                                                         wire:click="createComment({{$firstAnswer->id}},{{$loop->iteration}})">
                                            </flux:button>
                                        @endif
                                    @endif
                                </td>
                            @endif
                            <td class="px-3 py-2 text-center">{{ $index + 1 }}</td>
                            @foreach($questions as $question)
                                @php
                                    $answerData = $row->firstWhere('question.question', $question)?->answer;
                                    $isBinary = $answerData?->type === QuestionType::BOOLEAN->value;
                                    $value = $answerData?->answer ?? null;
                                @endphp
                                <td class="px-3 py-2">
                                    @if($isBinary)
                                        <div class="flex justify-center items-center h-full">
                                            @if($value)
                                                <flux:icon.check-circle class="text-green-500 size-5"/>
                                            @else
                                                <flux:icon.x-circle class="text-red-500 size-5"/>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-left max-w-[175px]">
                                            {{ $value ?? '-' }}
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-3 py-2 max-w-[150px]">
                                {{ $firstAnswerWithComment?->comment ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-2 text-center" colspan="{{ count($questions) + ($therapy->status === TherapyStatus::IN_PROGRESS ? 2 : 1) }}">
                                <flux:heading size="md" class="mt-2">Belum ada catatan aksi</flux:heading>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

@script
<script>
    const canvas = document.getElementById('committedActionChart');
    const ctx = canvas?.getContext('2d');
    const isDark = document.documentElement.classList.contains('dark');

    if (ctx) {
        const config = {
            type: 'doughnut',
            data: {
                labels: @json($labels),
                datasets: [{
                    data: @json($data),
                    borderWidth: 0.5,
                    // backgroundColor: ['#00C951', '#FB2D37'],
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

        new Chart(ctx, config);
    }
</script>
@endscript

