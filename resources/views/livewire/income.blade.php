<?php

use App\Enum\OrderStatus;
use App\Enum\TherapyStatus;
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
            'orders' => Order::where('status', OrderStatus::SETTLEMENT->value)
                ->latest()
                ->paginate(15),
        ];
    }

    protected function getDoctorOrders($user)
    {
        return [
            'orders' => Order::where('status', OrderStatus::SETTLEMENT->value)
                ->whereHas('therapy', function ($query) use ($user) {
                    $query->where('doctor_id', $user->doctor->id)
                        ->where('status', TherapyStatus::COMPLETED->value);
                })
                ->latest()
                ->paginate(15),
        ];
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <section class="w-full">
            @include('partials.main-heading', ['title' => 'Pendapatan'])
            <div class="relative rounded-lg px-6 py-4 bg-zinc-100 dark:bg-zinc-700">
                <flux:subheading>Total Pendapatan</flux:subheading>
                <flux:heading size="xl" class="mb-2">@currency($total_income)</flux:heading>
            </div>

            @foreach($orders as $order)
                <div class="mt-6 flex items-center justify-between">
                    <div>
                        <flux:heading size="lg" class="text-green-600">
                            +
                            @can('isAdmin', Auth::user())
                                @currency($order->therapy->application_fee)
                            @elsecan('isDoctor', Auth::user())
                                @currency($order->therapy->doctor_fee)
                            @endcan
                        </flux:heading>
                        <flux:subheading> {{$order->created_at->format('d F Y')}}
                            @can('isAdmin', Auth::user())
                                , Order ID #{{$order->id}} (Biaya Jasa Aplikasi)
                            @elsecan('isDoctor', Auth::user())
                                (Biaya Jasa Terapi)
                            @endcan
                        </flux:subheading>
                    </div>

                    @can('isDoctor', Auth::user())
                        <div class="ml-auto">
                            <flux:button
                                icon-trailing="arrow-up-right"
                                wire:navigate :href="route('doctor.therapies.completed.detail', $order->therapy->id)">
                                Detail
                            </flux:button>
                        </div>
                    @endcan
                    <flux:modal :name="'detail-order-'.$order->id" class="max-w-4xl w-full">
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">Detail</flux:heading>
                            </div>
                        </div>
                    </flux:modal>
                </div>
                <flux:separator class="mt-6"/>
            @endforeach

            <div class="mt-auto">
                {{$orders->links()}}
            </div>

        </section>
    </div>
</div>
