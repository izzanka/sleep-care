<?php

use App\Enum\UserRole;
use App\Models\Order;
use App\Models\Therapy;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public int $total_income = 0;
    public int $total_doctor = 0;
    public int $total_patient = 0;
    public int $total_rating = 0;
    public $count_therapy;

    public function mount()
    {
        $user = Auth::user();
        $this->total_income = $user->balance;

        if (Gate::allows('isAdmin', $user)) {
            $this->loadAdminStats();
        } elseif (Gate::allows('isDoctor', $user)) {
            $this->loadDoctorStats($user);
        }
    }

    protected function loadAdminStats()
    {
        $this->total_doctor = User::where('role', UserRole::DOCTOR->value)->count();
        $this->total_patient = User::where('role', UserRole::PATIENT->value)->count();
        $this->count_therapy = DB::table('therapies')->whereNotNull('status')->selectRaw("
                                    COUNT(*) as total,
                                    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
                                ")->first();
    }

    protected function loadDoctorStats($user)
    {
        $this->total_rating = $user->doctor->averageRating ?? 0;
    }

    protected function getAdminData(): array
    {
        return [
            'orders' => Order::whereNotNull('status')->latest()->paginate(15),
        ];
    }

    protected function getDoctorData($user): array
    {
        return [
            'therapies' => Therapy::where('doctor_id', $user->doctor->id)
                ->latest()->paginate(15),
        ];
    }

    public function with()
    {
        $user = Auth::user();

        if (Gate::allows('isAdmin', $user)) {
            return $this->getAdminData();
        }

        if (Gate::allows('isDoctor', $user)) {
            return $this->getDoctorData($user);
        }

        return [];
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <section class="w-full">
            @include('partials.main-heading', ['title' => 'Dashboard'])

            @can('isDoctor', auth()->user())
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-700">
                        <flux:subheading>Total Pendapatan</flux:subheading>
                        <flux:heading size="xl" class="mb-2">@currency($total_income)</flux:heading>
                    </div>

                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-700">
                        <flux:subheading>Total Rating</flux:subheading>
                        <flux:heading size="xl" class="mb-2">{{$total_rating}} / 5</flux:heading>
                    </div>

                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-700">
                        <flux:subheading>Total Pasien/Terapi</flux:subheading>
                        <flux:heading size="xl" class="mb-2">{{$therapies->total()}}</flux:heading>
                    </div>
                </div>

                @if(auth()->user()->doctor->graduated_from == null || auth()->user()->doctor->about == null || auth()->user()->doctor->phone == null)
                    <flux:callout icon="information-circle" variant="secondary" inline>
                        <flux:callout.heading>Harap lengkapi profile anda!</flux:callout.heading>
                        <flux:callout.text>Profile anda belum lengkap. Silakan lengkapi informasi profile anda segera.</flux:callout.text>
                        <x-slot name="actions">
                            <flux:button href="{{route('settings.profile')}}">Perbarui Profile</flux:button>
                        </x-slot>
                    </flux:callout>
                @endif

                @if(auth()->user()->is_therapy_in_progress)
                    <flux:callout icon="exclamation-circle" color="blue" inline class="mb-5 mt-5">
                        <flux:callout.heading>Anda memiliki terapi yang sedang berlangsung!</flux:callout.heading>
                        <x-slot name="actions">
                            <flux:button variant="primary" href="{{route('doctor.therapies.in_progress.index')}}"
                                         icon:trailing="arrow-up-right">Cek Sekarang</flux:button>
                        </x-slot>
                    </flux:callout>
                    <livewire:calendar></livewire:calendar>
                @endif
            @endcan

            @can('isAdmin', auth()->user())
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-600">
                        <flux:subheading>Total Pendapatan</flux:subheading>
                        <flux:heading size="xl" class="mb-2">@currency($total_income)</flux:heading>
                    </div>

                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-600">
                        <flux:subheading>Total Psikolog</flux:subheading>
                        <flux:heading size="xl" class="mb-2">{{$total_doctor}}</flux:heading>
                    </div>

                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-600">
                        <flux:subheading>Total Pasien</flux:subheading>
                        <flux:heading size="xl" class="mb-2">{{$total_patient}}</flux:heading>
                    </div>

                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-600">
                        <flux:subheading>Total Terapi</flux:subheading>
                        <flux:heading size="xl" class="mb-2">{{ $count_therapy->total }}</flux:heading>

                        <div class="text-sm">
                            <p>Berlangsung: <strong>{{ $count_therapy->in_progress }}</strong></p>
                            <p>Selesai: <strong>{{ $count_therapy->completed }}</strong></p>
                        </div>
                    </div>
                </div>

                <flux:separator class="mt-4 mb-4"></flux:separator>

                <flux:heading>
                    Transaksi
                </flux:heading>

                <div class="overflow-x-auto rounded-lg mt-4">
                    <table class="min-w-full table-auto text-sm">
                        <thead class="bg-blue-400 text-white dark:bg-blue-600">
                        <tr>
                            <th class=" px-6 py-3 text-left font-medium">Aksi</th>
                            <th class=" px-6 py-3 text-left font-medium">No</th>
                            <th class=" px-6 py-3 text-left font-medium">ID</th>
                            <th class=" px-6 py-3 text-left font-medium">Pasien</th>
                            <th class=" px-6 py-3 text-left font-medium">Metode Pembayaran</th>
                            <th class=" px-6 py-3 text-left font-medium">Total Pembayaran</th>
                            <th class=" px-6 py-3 text-left font-medium">Status Pembayaran</th>
                            <th class=" px-6 py-3 text-left font-medium">Status</th>
                            <th class=" px-6 py-3 text-left font-medium">Dibuat</th>
                            <th class=" px-6 py-3 text-left font-medium">Diperbarui</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y">
                        @forelse($orders as $order)
                            <flux:modal :name="'detail-terapi-'.$order->therapy->id" class="w-full max-w-md md:max-w-lg lg:max-w-xl p-4 md:p-6">
                                <div class="space-y-6">
                                    <div>
                                        <flux:heading size="lg">Detail Terapi</flux:heading>
                                    </div>
                                    <flux:input readonly value="{{$order->therapy->doctor->user->name}}" label="Psikolog"></flux:input>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                                        <flux:input value="{{$order->therapy->start_date->format('d/m/Y')}}" label="Tanggal Mulai"></flux:input>
                                        <flux:input value="{{$order->therapy->end_date->format('d/m/Y')}}" label="Tanggal Selesai"></flux:input>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 mb-4">
                                        <flux:input value="{{ 'Rp ' . number_format($order->therapy->doctor_fee, 0, ',', '.') }}" label="Biaya Psikolog"></flux:input>
                                        <flux:input value="{{ 'Rp ' . number_format($order->therapy->application_fee, 0, ',', '.') }}" label="Biaya Aplikasi"></flux:input>
                                    </div>
                                    <flux:input readonly value="{{$order->therapy->status?->label() ?? '-'}}" label="Status"></flux:input>
                                </div>
                            </flux:modal>

                            <tr wire:key="{{$order->id}}">
                                <td class=" px-6 py-4">
                                    <flux:modal.trigger :name="'detail-terapi-'.$order->therapy->id">
                                        <flux:button size="xs" variant="primary">Detail Terapi</flux:button>
                                    </flux:modal.trigger>
                                </td>
                                <td class=" px-6 py-4 text-center">{{$loop->iteration}}</td>
                                <td class=" px-6 py-4">{{$order->id}}</td>
                                <td class=" px-6 py-4">{{$order->therapy->patient->name ?? '-'}}</td>
                                <td class=" px-6 py-4">{{$order->payment_type ?? '-'}}</td>
                                <td class=" px-6 py-4 text-center">@currency($order->total_price)</td>
                                <td class=" px-6 py-4">{{$order->payment_status->label()}}</td>
                                <td class=" px-6 py-4">{{$order->status->label()}}</td>
                                <td class=" px-6 py-4">{{$order->created_at->format('d/m/Y H:i')}}</td>
                                <td class=" px-6 py-4">{{ $order->updated_at ? $order->updated_at->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="10" class=" px-6 py-4 text-gray-500 dark:text-gray-400">
                                    Belum ada transaksi
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-auto">
                    {{$orders->links()}}
                </div>
            @endcan
        </section>
    </div>
</div>
