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
        $this->count_therapy = DB::table('therapies')->selectRaw("
                                    COUNT(*) as total,
                                    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
                                ")->first();
    }

    protected function loadDoctorStats($user)
    {
        $this->total_rating = $user->doctor->averageRating ?? 0;
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

    protected function getAdminData(): array
    {
        return [
            'orders' => Order::latest()->paginate(15),
        ];
    }

    protected function getDoctorData($user): array
    {
        return [
            'therapies' => Therapy::where('doctor_id', $user->doctor->id)
                ->latest()->paginate(15),
        ];
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
                @if($therapies->isNotEmpty())
                    <livewire:calendar></livewire:calendar>
                @endif
            @endcan

            @can('isAdmin', auth()->user())
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-700">
                        <flux:subheading>Total Pendapatan</flux:subheading>
                        <flux:heading size="xl" class="mb-2">@currency($total_income)</flux:heading>
                    </div>

                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-700">
                        <flux:subheading>Total Psikolog</flux:subheading>
                        <flux:heading size="xl" class="mb-2">{{$total_doctor}}</flux:heading>
                    </div>

                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-700">
                        <flux:subheading>Total Pasien</flux:subheading>
                        <flux:heading size="xl" class="mb-2">{{$total_patient}}</flux:heading>
                    </div>

                    <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-700">
                        <flux:subheading>Total Terapi</flux:subheading>
                        <flux:heading size="xl" class="mb-2">{{ $count_therapy->total }}</flux:heading>

                        <div class="text-sm text-zinc-600 dark:text-zinc-300">
                            <p>Berlangsung: <strong>{{ $count_therapy->in_progress }}</strong></p>
                            <p>Selesai: <strong>{{ $count_therapy->completed }}</strong></p>
                        </div>
                    </div>

                </div>
                <flux:heading>Transaksi</flux:heading>
                <flux:separator class="mt-4 mb-4"/>

                <div class="overflow-x-auto shadow-lg rounded-lg border border-transparent dark:border-transparent">
                    <table class="min-w-full table-auto text-sm text-gray-900 dark:text-gray-100">
                        <thead class="bg-zinc-100 text-gray-600 dark:bg-zinc-800 dark:text-gray-200">
                        <tr class="border-b">
                            <th class="px-6 py-3 text-left font-medium">No</th>
                            <th class="px-6 py-3 text-left font-medium">Terapi ID</th>
                            <th class="px-6 py-3 text-left font-medium">Pasien</th>
                            <th class="px-6 py-3 text-left font-medium">Metode Pembayaran</th>
                            <th class="px-6 py-3 text-left font-medium">Status Pembayaran</th>
                            <th class="px-6 py-3 text-left font-medium">Total Pembayaran</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                            <th class="px-6 py-3 text-left font-medium">Dibuat Pada</th>
                            <th class="px-6 py-3 text-left font-medium">Diperbarui Pada</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-zinc-800 dark:divide-zinc-600">
                        @forelse($orders as $order)
                            <tr>
                                <td class="px-6 py-4 text-center">{{$loop->iteration}}</td>
                                <td class="px-6 py-4 text-center">{{$order->therapy_id}}</td>
                                <td class="px-6 py-4">
                                    <flux:link wire:navigate href="#">
                                        {{$order->therapy->patient->name ?? '-'}}
                                    </flux:link>
                                </td>
                                <td class="px-6 py-4">{{$order->payment_method}}</td>
                                <td class="px-6 py-4">{{$order->payment_status->label()}}</td>
                                <td class="px-6 py-4 text-center">@currency($order->total_price)</td>
                                <td class="px-6 py-4">{{$order->status->label()}}</td>
                                <td class="px-6 py-4">{{$order->created_at->format('d/m/Y H:i')}}</td>
                                <td class="px-6 py-4">{{ $order->updated_at ? $order->updated_at->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="9" class="px-6 py-4 text-gray-500 dark:text-gray-400">
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
