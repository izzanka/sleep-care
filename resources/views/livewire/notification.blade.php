<?php

use App\Enum\UserRole;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component {
    public function mark($notification_id)
    {
        DB::table('notifications')->where('id', $notification_id)->update(['read_at' => now()]);
    }

}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <section class="w-full">
        @include('partials.main-heading', ['title' => 'Notifikasi'])

        @forelse(auth()->user()->notifications()->paginate(15) as $notification)
            <div class="flex justify-between p-2">
                <flux:heading>
                    [{{$notification->created_at}}]
                    @if($notification->data['role'] == UserRole::DOCTOR->value)
                        Psikolog
                    @elseif($notification->data['role'] == UserRole::PATIENT->value)
                        Pasien
                    @endif {{$notification->data['name']}} ({{$notification->data['email']}}) baru saja melakukan
                    registrasi.
                </flux:heading>
                @if($notification->read_at == null)
                    <flux:button size="sm" icon="x-mark" variant="ghost" inset
                                 wire:click="mark('{{$notification->id}}')"/>
                @endif
            </div>
        @empty
            <flux:heading class="mt-6">
                Belum ada notifikasi
            </flux:heading>
        @endforelse

        <div class="mt-auto">
            {{auth()->user()->notifications()->paginate(15)->links()}}
        </div>
    </section>

    {{--        <div class="grid auto-rows-min gap-4 md:grid-cols-3">--}}
    {{--            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">--}}
    {{--                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />--}}
    {{--            </div>--}}
    {{--            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">--}}
    {{--                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />--}}
    {{--            </div>--}}
    {{--            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">--}}
    {{--                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">--}}
    {{--            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />--}}
    {{--        </div>--}}
</div>
