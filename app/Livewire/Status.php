<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class Status extends Component
{
    public int $userId;

    public bool $is_active;

    public function mount(int $id, bool $is_active)
    {
        $this->userId = $id;
        $this->is_active = $is_active;
    }

    public function updatedIsActive($value)
    {
        User::select('id', 'is_active')->where('id', $this->userId)->update(['is_active' => $this->is_active]);

        session()->flash('status', ['message' => 'Status pengguna berhasil diubah.', 'success' => true]);

        return redirect()->route('admin.users.doctor');
    }

    public function render()
    {
        return view('livewire.status');
    }
}
