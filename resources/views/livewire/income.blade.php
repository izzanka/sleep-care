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

        if (Gate::allows('isDoctor', $user)) {
            $user->load('doctor');
        }
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
            'orders' => Order::with('therapy')
                ->where('status', OrderStatus::SUCCESS->value)
                ->latest()
                ->paginate(15),
        ];
    }

    protected function getDoctorOrders($user)
    {
        return [
            'orders' => Order::with('therapy')
                ->where('status', OrderStatus::SUCCESS->value)
                ->whereHas('therapy', fn($query) =>
                $query->where('doctor_id', $user->doctor->id)
                )
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

            @forelse($orders as $order)
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
                        <flux:subheading>{{$order->created_at->format('d F Y')}}
                            @can('isAdmin', Auth::user())
                                (Biaya Jasa Aplikasi)
                            @elsecan('isDoctor', Auth::user())
                                (Biaya Jasa Terapi)
                            @endcan
                        </flux:subheading>
                    </div>

                    <div class="ml-auto">
                        <flux:modal.trigger :name="'detail-order-'.$order->id">
                            <flux:button
                                icon-trailing="arrow-up-right"
                            >
                                Detail
                            </flux:button>
                        </flux:modal.trigger>
                    </div>

{{--                        <flux:modal :name="'detail-order-'.$order->id" class="md:w-100">--}}
{{--                            <div class="space-y-6">--}}
{{--                                <div>--}}
{{--                                    <flux:heading size="lg">Detail</flux:heading>--}}
{{--                                </div>--}}

{{--                                <flux:heading>Pasien</flux:heading>--}}
{{--                                <flux:subheading>--}}
{{--                                    <flux:link wire:navigate href="#">--}}
{{--                                        {{$order->therapy->patient->name}}--}}
{{--                                    </flux:link>--}}
{{--                                </flux:subheading>--}}

{{--                                <flux:heading>Psikolog</flux:heading>--}}
{{--                                <flux:subheading>--}}
{{--                                    <flux:link wire:navigate href="#">--}}
{{--                                        {{$order->therapy->doctor->user->name}}--}}
{{--                                    </flux:link>--}}
{{--                                </flux:subheading>--}}

{{--                                <flux:heading>Status</flux:heading>--}}
{{--                                <flux:subheading>--}}
{{--                                    {{$order->status->label()}}--}}
{{--                                </flux:subheading>--}}

{{--                                <flux:heading>Status Pembayaran</flux:heading>--}}
{{--                                <flux:subheading>--}}
{{--                                    {{$order->payment_status->label()}}--}}
{{--                                </flux:subheading>--}}

{{--                                <flux:heading>Metode Pembayaran</flux:heading>--}}
{{--                                <flux:subheading>--}}
{{--                                    {{$order->payment_method}}--}}
{{--                                </flux:subheading>--}}

{{--                                <flux:heading>Total Harga</flux:heading>--}}
{{--                                <flux:subheading>--}}
{{--                                    @currency($order->total_price)--}}
{{--                                </flux:subheading>--}}
{{--                            </div>--}}
{{--                        </flux:modal>--}}
                </div>
                <flux:separator class="mt-6"/>
            @empty
                <flux:heading class="mt-6">
                    Belum ada pendapatan
                </flux:heading>
            @endforelse

            <div class="mt-auto">
                {{$orders->links()}}
            </div>

        </section>
    </div>
</div>
