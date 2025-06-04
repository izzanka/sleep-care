<?php

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
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center p-3 sm:p-4 rounded-lg mt-3 sm:mt-4 {{ $notification->read_at ? 'bg-white dark:bg-zinc-800' : 'bg-zinc-100 dark:bg-zinc-700' }}">
                <div class="flex-1">
                    <flux:heading class="text-sm">
                        <span class="text-xs sm:text-sm font-normal text-gray-500 dark:text-gray-400">
                            [{{ $notification->created_at->format('d/m/Y H:i') }}]
                        </span>
                        <span class="block sm:inline mt-1 sm:mt-0 sm:ml-2">
                            {{ $notification->data['message'] }}
                        </span>
                    </flux:heading>
                </div>

                @if (is_null($notification->read_at))
                    <div class="mt-2 sm:mt-0 sm:ml-4">
                        <flux:button
                            size="xs"
                            icon="x-mark"
                            variant="ghost"
                            inset
                            wire:click="mark('{{ $notification->id }}')"
                            class="dark:text-white"
                        />
                    </div>
                @endif
            </div>
        @empty
            <flux:heading class="mt-6 text-center sm:text-left">
                Belum ada notifikasi
            </flux:heading>
        @endforelse

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </section>
</div>
