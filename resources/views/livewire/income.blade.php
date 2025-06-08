<?php

use App\Enum\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public int $total_income = 0;

    public function mount()
    {
        $user = Auth::user();
        $this->total_income = $user->balance;
    }

    public function with(): array
    {
        $user = Auth::user();

        if (Gate::allows('isAdmin', $user)) {
            return $this->getAdminOrders();
        }

        if (Gate::allows('isDoctor', $user)) {
            return $this->getDoctorOrders($user);
        }

        return [];
    }

    protected function getAdminOrders()
    {
        return [
            'orders' => Order::where('status', OrderStatus::SUCCESS->value)
                ->latest()
                ->paginate(15),
        ];
    }

    protected function getDoctorOrders($user)
    {
        return [
            'orders' => Order::where('status', OrderStatus::SUCCESS->value)
                ->whereHas('therapy', function ($query) use ($user) {
                    $query->where('doctor_id', $user->doctor->id);
                })
                ->latest()
                ->paginate(15),
        ];
    }
}; ?>

<div class="w-full">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <section class="w-full">
            @include('partials.main-heading', ['title' => 'Pendapatan'])

            <div class="relative rounded-lg px-4 sm:px-6 py-4 bg-zinc-100 dark:bg-zinc-700">
                <flux:subheading class="text-sm">Total Pendapatan</flux:subheading>
                <flux:heading size="xl" class="mb-2 text-lg sm:text-xl md:text-2xl">@currency($total_income)</flux:heading>
            </div>

            @foreach($orders as $order)
                <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="w-full">
                        <flux:heading size="lg" class="text-green-600 text-base">
                            +
                            @can('isAdmin', Auth::user())
                                @currency($order->therapy->application_fee)
                            @elsecan('isDoctor', Auth::user())
                                @currency($order->therapy->doctor_fee)
                            @endcan
                        </flux:heading>

                        <flux:subheading class="text-sm">
                            <span>{{$order->created_at->isoFormat('D MMMM Y')}}</span>
                            <span class="block sm:inline mt-1 sm:mt-0">
                                @can('isAdmin', Auth::user())
                                    (Biaya Jasa Aplikasi, Transaksi ID: {{$order->id}})
                                @elsecan('isDoctor', Auth::user())
                                    (Biaya Jasa Terapi Pasien {{$order->therapy->patient?->name ?? '- '}})
                                @endcan
                            </span>
                        </flux:subheading>
                    </div>
                </div>

                <flux:separator class="mt-4 mb-4"/>
            @endforeach

            <div class="mt-6 sm:mt-8 flex justify-center sm:justify-start">
                {{$orders->links()}}
            </div>
        </section>
    </div>
</div>
