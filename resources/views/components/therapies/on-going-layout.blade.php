<div class="flex items-start max-md:flex-col">
    <div class="mr-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('doctor.therapies.in_progress.index')" :current="request()->routeIs('doctor.therapies.in_progress.index') || request()->routeIs('doctor.therapies.in_progress.chat')" wire:navigate>Berlangsung</flux:navlist.item>
            <flux:navlist.item :href="route('doctor.therapies.in_progress.schedule')" :current="request()->routeIs('doctor.therapies.in_progress.schedule')" wire:navigate>Jadwal</flux:navlist.item>
            <flux:navlist.group expandable heading="Catatan">
                <flux:navlist.item :href="route('doctor.therapies.records.sleep_diary')" :current="request()->routeIs('doctor.therapies.records.sleep_diary')" wire:navigate>Sleep Diary</flux:navlist.item>
                <flux:navlist.item :href="route('doctor.therapies.records.identify_value')" :current="request()->routeIs('doctor.therapies.records.identify_value')" wire:navigate>Identify Value</flux:navlist.item>
                <flux:navlist.item :href="route('doctor.therapies.records.thought_record')" :current="request()->routeIs('doctor.therapies.records.thought_record')" wire:navigate>Thought Record</flux:navlist.item>
                <flux:navlist.item href="#" wire:navigate>Emotion Record</flux:navlist.item>
                <flux:navlist.item href="#" wire:navigate>Committed Action</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <div class="w-full">
            {{ $slot }}
        </div>
    </div>
</div>
