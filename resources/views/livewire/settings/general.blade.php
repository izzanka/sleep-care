<?php

use App\Models\General;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component {
    public int $id;
    public int $doctor_fee;
    public int $application_fee;
    public bool $is_himpsi;

    public function mount()
    {
        $setting = General::first();
        $this->id = $setting->id;
        $this->doctor_fee = $setting->doctor_fee;
        $this->application_fee = $setting->application_fee;
        $this->is_himpsi = $setting->is_himpsi;
    }

    public function updateSetting()
    {
        $validated = $this->validate([
            'doctor_fee' => ['required', 'int', 'min:1'],
            'application_fee' => ['required', 'int', 'min:1'],
            'is_himpsi' => ['required', 'boolean'],
        ]);

        General::first()->update($validated);

        Session::flash('status', ['message' => 'Pengaturan umum berhasil diubah.', 'success' => true]);
    }

}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <section class="w-full">
        @include('partials.main-heading', ['title' => 'Umum'])
        <form wire:submit="updateSetting" >
            <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input wire:model="application_fee" label="Biaya Aplikasi" type="text" name="application_fee"
                            required/>
                <flux:input wire:model="doctor_fee" label="Biaya Jasa Psikolog" type="text" name="doctor_fee" required/>
            </div>
            <flux:separator class="mt-4 mb-4"/>
            <div class="w-full">
                <flux:checkbox wire:model="is_himpsi" label="Aktifkan verifikasi HIMPSI" description="Verifikasi akan dilakukan sebelum psikolog mendaftar."
                />
            </div>
            <div class="md:col-span-2 flex mt-5">
                <flux:button variant="primary" type="submit" class="w-full md:w-auto">Simpan</flux:button>
            </div>
        </form>

    </section>
</div>
