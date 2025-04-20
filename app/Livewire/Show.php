<?php

namespace App\Livewire;

use App\Models\Question;
use Livewire\Component;

class Show extends Component
{
    public int $questionID;

    public bool $is_show;

    public function mount(int $id, bool $is_show)
    {
        $this->questionID = $id;
        $this->is_show = $is_show;
    }

    public function updatedIsShow($value)
    {
        Question::select('id', 'is_show')->where('id', $this->questionID)->update(['is_show' => $this->is_show]);

        session()->flash('status', ['message' => 'Status pertanyaan berhasil diubah.', 'success' => true]);

        return redirect()->route('admin.settings.question');
    }

    public function render()
    {
        return view('livewire.show');
    }
}
