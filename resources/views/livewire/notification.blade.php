<?php

use App\Enum\UserRole;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;

new class extends Component {
    public function mark(string $notification_id): void
    {
        DB::table('notifications')
            ->where('id', $notification_id)
            ->update(['read_at' => now()]);
    }

    public function with()
    {
        return [
            'notifications' => auth()->user()->notifications()->paginate(15),
        ];
    }

}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <section class="w-full">
        @include('partials.main-heading', ['title' => 'Notifikasi'])

        @forelse ($notifications as $notification)
            <div class="flex justify-between p-2 {{$notification->read_at ? '' : 'bg-zinc-500'}} rounded-lg mt-4">
                <flux:heading>
                    [{{ $notification->created_at }}]
                    @switch($notification->data['role'])
                        @case(UserRole::DOCTOR->value)
                            Psikolog
                            @break
                        @case(UserRole::PATIENT->value)
                            Pasien
                            @break
                    @endswitch
                    {{ $notification->data['name'] }} ({{ $notification->data['email'] }}) {{ $notification->data['message'] }}
                </flux:heading>

                @if (is_null($notification->read_at))
                    <flux:button size="sm" icon="x-mark" variant="ghost" inset
                                 wire:click="mark('{{ $notification->id }}')" />
                @endif
            </div>
        @empty
            <flux:heading class="mt-6">
                Belum ada notifikasi
            </flux:heading>
        @endforelse

        <div class="mt-auto">
            {{ $notifications->links() }}
        </div>
    </section>
</div>
