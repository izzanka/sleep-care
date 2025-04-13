<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Status extends Component
{
    public int $id;
    public bool $is_active;

    public function updatedIsActive($value)
    {
        $user = User::find($this->id);
        $user->update([
            'is_active' => $this->is_active,
        ]);

        session()->flash('status', ['message' => 'Status pengguna berhasil diubah.', 'success' => true]);
        $this->redirectRoute('admin.users.doctor');
    }

    public function mount($id, $is_active)
    {
        $this->id = $id;
        $this->is_active = $is_active;
    }

    public function render()
    {
        return view('livewire.status');
    }
}
