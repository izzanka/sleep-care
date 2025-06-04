<?php

use App\Models\General;
use App\Service\GeneralService;
use Livewire\Volt\Component;

new class extends Component {
    public int $id;
    public int $doctor_fee;
    public int $application_fee;
    public bool $is_himpsi;
    public $updated_at;

    protected GeneralService $generalService;

    public function boot(GeneralService $generalService)
    {
        $this->generalService = $generalService;
    }

    public function mount()
    {
        $setting = $this->generalService->get();
        $this->id = $setting->id;
        $this->doctor_fee = $setting->doctor_fee;
        $this->application_fee = $setting->application_fee;
        $this->is_himpsi = $setting->is_himpsi;
        $this->updated_at = $setting->updated_at;
    }

    public function updateSetting()
    {
        $validated = $this->validate([
            'doctor_fee' => ['required', 'int', 'min:1'],
            'application_fee' => ['required', 'int', 'min:1'],
            'is_himpsi' => ['required', 'boolean'],
        ]);

        General::first()->update($validated);

        session()->flash('status', ['message' => 'Pengaturan umum berhasil diubah.', 'success' => true]);

        $this->redirectRoute('admin.settings.general');
    }

}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <section class="w-full">
        @include('partials.main-heading', ['title' => 'Umum'])
        <form wire:submit="updateSetting">
            <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:input wire:model="application_fee" label="Biaya Jasa Aplikasi" type="text" name="application_fee"
                            required/>
                <flux:input wire:model="doctor_fee" label="Biaya Jasa Psikolog" type="text" name="doctor_fee" required/>
            </div>
            <div class="w-full mt-4">
                <flux:checkbox wire:model="is_himpsi" label="Aktifkan verifikasi HIMPSI"
                               description="Verifikasi akan dilakukan sebelum psikolog mendaftar."
                />
            </div>
            <flux:separator class="mt-4 mb-4"/>
            <div class="md:col-span-2 flex mt-5">
                <flux:button variant="primary" type="submit" class="w-full md:w-auto">Simpan</flux:button>
            </div>
            <flux:text class="mt-2 text-xs">
                Terakhir Diperbarui Pada: {{ $updated_at ? $updated_at->format('d/m/Y H:i') : '-' }}
            </flux:text>
        </form>
    </section>
</div>
