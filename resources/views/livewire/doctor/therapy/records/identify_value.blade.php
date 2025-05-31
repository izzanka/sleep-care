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
    public int $id;
    public int $no;
    public ?string $comment;

    public function boot(TherapyService $therapyService,
                         RecordService  $recordService, QuestionService $questionService)
    {
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
        $this->identifyValue = $this->recordService->getIdentifyValue($this->therapy->id);
        $this->labels = $this->getUniqueComments();
    }

    protected function getDatasetLabels()
    {
        return $this->questionService->get('identify_value')->pluck('question')
            ->map(fn($q) => explode(',', $q)[0])->toArray();
    }

    protected function getUniqueComments()
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

    public function createComment(int $id, int $no)
    {
        $questionAnswer = IdentifyValueQuestionAnswer::find($id);
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

        $questionAnswer = IdentifyValueQuestionAnswer::find($this->id);
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
        $questionAnswer = IdentifyValueQuestionAnswer::find($id);
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
    @include('partials.main-heading', ['title' => null])

    @if($therapy->status === TherapyStatus::IN_PROGRESS)
        <flux:callout icon="information-circle" class="mb-4" color="blue"
                      x-data="{ visible: localStorage.getItem('hideMessageValue') !== 'true' }"
                      x-show="visible"
                      x-init="$watch('visible', value => !value && localStorage.setItem('hideMessageValue', 'true'))">
            <flux:callout.heading>Catatan Nilai (Identify Value)</flux:callout.heading>

            <flux:callout.text>
                Digunakan untuk membantu pasien mencatat dan mengenali hal-hal yang bermakna dalam hidup mereka dan menjadi arah dalam mengambil tindakan sesuai tujuan hidup.
                <br><br>
                <flux:callout.link href="#" @click="visible = false">Jangan tampilkan lagi.</flux:callout.link>
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="flex" wire:ignore>
            <div class="w-full max-w-lg flex-shrink-0 mx-auto">
                <canvas id="identifyValueChart" class="w-full h-80"></canvas>
            </div>
        </div>
        <flux:separator class="mb-4"/>
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
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto text-sm rounded-lg border overflow-hidden">
                <thead class="bg-blue-400 dark:bg-blue-600 text-white">
                <tr class="text-left">
                    @if($therapy->status === TherapyStatus::IN_PROGRESS)
                    <th class="px-4 py-2 font-medium">Aksi Komentar</th>
                    @endif
                    <th class="px-4 py-2 font-medium">No</th>
                    <th class="px-4 py-2 font-medium">Area</th>
                    <th class="px-4 py-2 font-medium">{{ $datasetLabels[0] }}</th>
                    <th class="px-4 py-2 font-medium">{{ $datasetLabels[2] }}</th>
                    <th class="px-4 py-2 font-medium">{{ $datasetLabels[1] }}</th>
                    <th class="px-4 py-2 font-medium">Komentar</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($labels as $index => $label)
                    @php
                        $questionAnswer = $identifyValue->questionAnswers[$index] ?? null;
                    @endphp
                    <tr class="text-left" wire:key="{{$index}}">
                        @if($therapy->status === TherapyStatus::IN_PROGRESS)
                            <td class="px-4 py-2 text-center">
                                @if($identifyValue->questionAnswers[$index]->comment)
                                    <div class="flex items-center space-x-1">
                                        <flux:button variant="primary" size="xs" icon="pencil-square" wire:click="createComment({{ $questionAnswer->id }}, {{ $loop->iteration }})" />
                                        <flux:button variant="danger" size="xs" icon="trash" wire:confirm="Apa anda yakin ingin menghapus komentar ini?" wire:click="deleteComment({{ $questionAnswer->id }})" />
                                    </div>
                                @else
                                    <flux:button variant="primary" size="xs" icon="plus" wire:click="createComment({{$questionAnswer->id}},{{$loop->iteration}})">
                                    </flux:button>
                                @endif
                            </td>
                        @endif
                        <td class="px-4 py-2 text-center">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2">{{ $label }}</td>
                        <td class="px-4 py-2 text-center">{{ $numberAnswers['Skala Kepentingan'][$index] }}</td>
                        <td class="px-4 py-2 text-center">{{ $numberAnswers['Skor Kesesuaian'][$index] }}</td>
                        <td class="px-4 py-2">
                            {{ $textAnswers[$datasetLabels[1]][$index] ?? '-' }}
                        </td>
                        <td class="px-4 py-2">
                            {{ $identifyValue->questionAnswers[$index]->comment ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-2 text-center" colspan="7">
                            <flux:heading class="mt-2">Belum ada catatan nilai</flux:heading>
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
    const canvas = document.getElementById('identifyValueChart');
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

    const chart = new Chart(ctx, config);
</script>
@endscript
