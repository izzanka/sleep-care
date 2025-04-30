<?php

use App\Enum\RecordType;
use App\Enum\QuestionType;
use App\Models\Question;
use Flux\Flux;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public ?string $filterType = null;
    public ?string $filterRecordType = null;
    public ?int $filterParentID = null;
    public ?bool $filterIsParent = null;
    public ?int $filterID = null;

    public ?int $ID = null;
    public ?string $question = null;
    public ?string $type = null;
    public ?string $record_type = null;
    public ?int $parent_id = null;
    public bool $is_parent = false;
    public ?string $note = null;

    public function with()
    {
        $query = Question::query();

        if ($this->search != '') {
            $this->resetPage();
            $query = Question::search($this->search);
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterRecordType) {
            $query->where('record_type', $this->filterRecordType);
        }

        if ($this->filterParentID) {
            $query->where('parent_id', $this->filterParentID);
        }

        if ($this->filterID) {
            $query->where('id', $this->filterID);
        }

        if (is_bool($this->filterIsParent)) {
            $query->where('is_parent', $this->filterIsParent);
        }

        return [
            'questions' => $query->latest()->paginate(15),
        ];
    }

    public function updatedFilterIsParent($value)
    {
        $this->filterIsParent = $value === "true" ? true : ($value === "false" ? false : null);
    }

    public function filterByParentID(int $id)
    {
        $this->filterID = $id;
    }

    public function filter()
    {
        $this->validate([
            'filterType' => ['nullable','string'],
            'filterRecordType' => ['nullable','string'],
            'filterParentID' => ['nullable','int'],
            'filterIsParent' => ['nullable','bool'],
            'filterID' => ['nullable','int'],
        ]);

        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->reset(['filterType','filterRecordType','filterParentID','filterIsParent','filterID']);
        $this->resetValidation(['filterType','filterRecordType','filterParentID','filterIsParent','filterID']);
    }

    public function resetEdit()
    {
        $this->resetValidation(['question','type','record_type','parent_id','is_parent','note']);
    }

    public function editQuestion(int $questionID)
    {
        $question = Question::select('id', 'question', 'type', 'record_type', 'parent_id', 'is_parent', 'note')->find($questionID);
        if(!$question){
            session()->flash('status', ['message' => 'Pertanyaan catatan terapi tidak dapat ditemukan.', 'success' => false]);
        }
        $this->ID = $questionID;
        $this->question = $question->question;
        $this->type = $question->type->value;
        $this->record_type = $question->record_type->value;
        $this->parent_id = $question->parent_id;
        $this->is_parent = $question->is_parent;
        $this->note = $question->note;

        $this->modal('editQuestion')->show();
    }

    public function updatedIsParent($value)
    {
        $this->is_parent = $value === "true" ? true : ($value === "false" ? false : null);
    }

    public function updateQuestion(int $questionID)
    {
        $validated = $this->validate([
            'question' => ['required', 'string', 'max:225'],
            'type' => ['required'],
            'record_type' => ['required'],
            'parent_id' => ['nullable', 'int'],
            'is_parent' => ['boolean'],
            'note' => ['nullable', 'string', 'max:225'],
        ]);

        $question = Question::find($questionID);

        if(!$question){
            session()->flash('status', ['message' => 'Pertanyaan catatan terapi tidak dapat ditemukan.', 'success' => false]);
        }

        $question->update($validated);

        $this->modal('editQuestion')->close();

        session()->flash('status', ['message' => 'Pertanyaan catatan terapi berhasil diubah.', 'success' => true]);

        $this->js(
            "window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });"
        );
    }

    public function destroyQuestion(int $questionID)
    {
        $question = Question::find($questionID);

        if(!$question){
            session()->flash('status', ['message' => 'Pertanyaan catatan terapi tidak dapat ditemukan.', 'success' => false]);
        }

        $question->delete();

        $this->modal('deleteQuestion')->close();

        session()->flash('status', ['message' => 'Pertanyaan catatan terapi berhasil dihapus.', 'success' => true]);

        $this->js(
            "window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });"
        );
    }

}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <section class="w-full">
        @include('partials.main-heading', ['title' => 'Pertanyaan Catatan Terapi'])

        <flux:modal name="editQuestion" class="w-full max-w-md md:max-w-lg lg:max-w-xl p-4 md:p-6">
            <div class="space-y-6">
                <form wire:submit="updateQuestion({{$ID}})">
                    <!-- Modal Header -->
                    <div>
                        <flux:heading size="lg">Ubah Pertanyaan Catatan Terapi</flux:heading>
                    </div>

                    <div class="mt-4">
                        <flux:heading>ID</flux:heading>
                        <flux:text class="mb-4">{{$ID}}</flux:text>
                        <flux:textarea wire:model="question" label="Pertanyaan"></flux:textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                        <flux:select label="Jenis Pertanyaan" wire:model="type">
                            @foreach(QuestionType::cases() as $questionType)
                                <flux:select.option
                                    :value="$questionType">{{$questionType->label()}}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select label="Jenis Catatan Terapi" wire:model="record_type">
                            @foreach(RecordType::cases() as $recordType)
                                <flux:select.option :value="$recordType">{{$recordType->label()}}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                        <flux:select label="Pertanyaan Induk" wire:model="is_parent">
                            <flux:select.option value="false">Tidak
                            </flux:select.option>
                            <flux:select.option value="true">Ya
                            </flux:select.option>
                        </flux:select>
                        <flux:input wire:model="parent_id" label="ID Pertanyaan Induk"></flux:input>
                    </div>

                    <div class="mt-4 mb-4">
                        <flux:textarea wire:model="note" label="Catatan"></flux:textarea>
                    </div>

                    <flux:button type="submit" variant="primary" class="w-full">Simpan</flux:button>
                </form>
            </div>
        </flux:modal>

        <flux:modal name="deleteQuestion" class="min-w-[22rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Hapus pertanyaan?</flux:heading>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <form wire:submit="destroyQuestion({{$ID}})">
                        <flux:button type="submit" variant="danger">Hapus pertanyaan</flux:button>
                    </form>
                </div>
            </div>
        </flux:modal>

        <div x-data="{showFilter: false}">
            <div class="flex items-center">
                <flux:input icon="magnifying-glass" placeholder="Cari pertanyaan catatan terapi"
                            wire:model.live="search"/>
                {{--                <flux:button class="ml-2" variant="primary">Cari</flux:button>--}}
            </div>
            <div>
                <flux:button @click="showFilter = !showFilter" class="mt-4 w-full">
                    Filter
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="w-4 h-4 transition-transform duration-300"
                        :class="showFilter ? 'rotate-180' : ''"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </flux:button>

            </div>

            <flux:separator class="mt-4 mb-4"/>
            <div x-show="showFilter" x-transition>
                <form wire:submit="filter">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                        <div>
                            <flux:select label="Jenis Pertanyaan" wire:model="filterType">
                                <flux:select.option value="">Semua</flux:select.option>
                                @foreach(QuestionType::cases() as $questionType)
                                    <flux:select.option
                                        :value="$questionType">{{$questionType->label()}}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div>
                            <flux:select label="Jenis Catatan Terapi" wire:model="filterRecordType">
                                <flux:select.option value="">Semua</flux:select.option>
                                @foreach(RecordType::cases() as $recordType)
                                    <flux:select.option
                                        :value="$recordType">{{$recordType->label()}}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div>
                            <flux:select label="Pertanyaan Induk" wire:model="filterIsParent">
                                <flux:select.option value="">Semua</flux:select.option>
                                <flux:select.option value="true">Ya</flux:select.option>
                                <flux:select.option value="false">Tidak</flux:select.option>
                            </flux:select>
                        </div>

                        <div>
                            <flux:input label="ID Pertanyaan Induk" wire:model="filterParentID" placeholder="1"></flux:input>
                        </div>
                    </div>

                    <flux:button variant="primary" type="submit">Filter</flux:button>
                    <flux:button class="ms-2" variant="danger" wire:click="resetFilter">Reset</flux:button>
                </form>
                <flux:separator class="mt-4 mb-4"/>
            </div>
        </div>

        <div class="overflow-x-auto shadow-lg rounded-lg border border-transparent dark:border-transparent">
            <table class="min-w-full table-auto text-sm text-gray-900 dark:text-gray-100">
                <thead class="bg-zinc-100 text-gray-600 dark:bg-zinc-800 dark:text-gray-200">
                <tr class="border-b">
                    <th class="px-6 py-3 text-left font-medium">Aksi</th>
                    <th class="px-6 py-3 text-left font-medium">No</th>
{{--                    <th class="px-6 py-3 text-left font-medium">Tampilkan</th>--}}
                    <th class="px-6 py-3 text-left font-medium">Pertanyaan</th>
                    <th class="px-6 py-3 text-left font-medium">Pertanyaan Induk</th>
                    <th class="px-6 py-3 text-left font-medium">ID Pertanyaan Induk</th>
                    <th class="px-6 py-3 text-left font-medium">Jenis Pertanyaan</th>
                    <th class="px-6 py-3 text-left font-medium">Jenis Catatan Terapi</th>
                    <th class="px-6 py-3 text-left font-medium">Catatan</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-600">
                @forelse($questions as $question)
                    <tr wire:key="{{$question->id}}">
                        <td class="px-6 py-4">
{{--                            <div class="flex space-x-2">--}}
                                <flux:button size="xs" icon="pencil-square" class="me-1" wire:click="editQuestion({{$question->id}})"></flux:button>
{{--                                <flux:button size="xs" variant="danger" icon="trash" wire:click="destroyQuestion({{$question->id}})" wire:confirm="Apa anda yakin ingin menghapus pertanyaan ini?"></flux:button>--}}
{{--                            </div>--}}
                        </td>
                        <td class="px-6 py-4">{{ ($questions->currentPage() - 1) * $questions->perPage() + $loop->iteration }}</td>
{{--                        <td class="px-6 py-4">--}}
{{--                            <livewire:show :id="$question->id" :is_show="$question->is_show"--}}
{{--                                             :key="$question->id"></livewire:show>--}}
{{--                        </td>--}}
                        <td class="px-6 py-4">{{$question->question}}</td>
                        <td class="px-6 py-4">{{$question->is_parent ? 'Ya' : 'Tidak'}}</td>
                        <td class="px-6 py-4">
                            @if($question->parent_id)
                                <flux:link wire:click.prevent="filterByParentID({{$question->parent_id}})" href="#">
                                    {{$question->parent_id}}
                                </flux:link>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4">{{$question->type->label()}}</td>
                        <td class="px-6 py-4">{{$question->record_type->label()}}</td>
                        <td class="px-6 py-4">{{$question->note ?? '-'}}</td>
                    </tr>
                @empty
                    <tr class="text-center">
                        <td colspan="8" class="px-6 py-4 text-gray-500 dark:text-gray-400">
                            Kosong
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{$questions->links()}}
        </div>
    </section>
</div>
