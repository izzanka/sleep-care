<div class="flex items-start max-md:flex-col">
    <div class="mr-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('doctor.therapies.in_progress.index')" :current="request()->routeIs('doctor.therapies.in_progress.index')" wire:navigate>Berlangsung</flux:navlist.item>
            <flux:navlist.item :href="route('doctor.therapies.in_progress.schedule')" :current="request()->routeIs('doctor.therapies.in_progress.schedule')" wire:navigate>Jadwal</flux:navlist.item>
            <flux:navlist.item href="#" wire:navigate>Catatan</flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <div class="w-full">
            {{ $slot }}
        </div>
    </div>
</div>
