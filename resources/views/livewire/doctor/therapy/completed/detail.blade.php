<?php

use App\Enum\TherapyStatus;
use App\Service\TherapyService;
use Livewire\Volt\Component;

new class extends Component {
    protected TherapyService $therapyService;

    public $therapy;

    public function boot(TherapyService $therapyService)
    {
        $this->therapyService = $therapyService;
    }

    public function mount(int $id)
    {
        $this->therapy = $this->therapyService->find(status: TherapyStatus::COMPLETED->value, id: $id)[0];
    }
}; ?>

<section>
    @include('partials.main-heading', ['title' => 'Detail Riwayat'])
</section>
