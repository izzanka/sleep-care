<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 w-70">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="mr-5 flex items-center space-x-2" wire:navigate>
                <x-app-logo class="size-8" href="#"></x-app-logo>
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    <flux:navlist.item icon="inbox" badge="{{auth()->user()->unreadNotifications->count()}}" :href="route('notification')" wire:navigate :current="request()->routeIs('notification')">Notifikasi</flux:navlist.item>
                    <flux:navlist.item icon="banknotes" :href="route('income')" :current="request()->routeIs('income')" wire:navigate>Pendapatan</flux:navlist.item>
                    @can('isDoctor', auth()->user())
                        <flux:navlist.group expandable heading="Terapi">
                            <flux:navlist.group expandable heading="Berlangsung">
                                <flux:navlist.item :href="route('doctor.therapies.in_progress.index')" :current="request()->routeIs('doctor.therapies.in_progress.index')" wire:navigate>Halaman Utama</flux:navlist.item>
                                @if(auth()->user()
                                    ->doctor()
                                    ->whereHas('therapies', function ($query) {
                                        $query->where('status', \App\Enum\TherapyStatus::IN_PROGRESS->value);
                                    })
                                    ->exists())
                                    <flux:navlist.item :href="route('doctor.therapies.in_progress.schedule')" :current="request()->routeIs('doctor.therapies.in_progress.schedule')" wire:navigate>Jadwal</flux:navlist.item>
                                    <flux:navlist.item :href="route('doctor.therapies.in_progress.chat')" :current="request()->routeIs('doctor.therapies.in_progress.chat')" wire:navigate>Percakapan</flux:navlist.item>
                                    <flux:navlist.group expandable heading="Catatan">
                                        <flux:navlist.item :href="route('doctor.therapies.records.sleep_diary')" :current="request()->routeIs('doctor.therapies.records.sleep_diary')" wire:navigate>Tidur</flux:navlist.item>
                                        <flux:navlist.item :href="route('doctor.therapies.records.identify_value')" :current="request()->routeIs('doctor.therapies.records.identify_value')" wire:navigate>Nilai</flux:navlist.item>
                                        <flux:navlist.item :href="route('doctor.therapies.records.thought_record')" :current="request()->routeIs('doctor.therapies.records.thought_record')" wire:navigate>Pikiran</flux:navlist.item>
                                        <flux:navlist.item :href="route('doctor.therapies.records.emotion_record')" :current="request()->routeIs('doctor.therapies.records.emotion_record')" wire:navigate>Emosi</flux:navlist.item>
                                        <flux:navlist.item :href="route('doctor.therapies.records.committed_action')" :current="request()->routeIs('doctor.therapies.records.committed_action')" wire:navigate>Tindakan</flux:navlist.item>
                                    </flux:navlist.group>
                                @endif
                            </flux:navlist.group>
{{--                            <flux:navlist.item :href="route('doctor.therapies.in_progress.index')" badge="{{auth()->user()->doctor->therapies->where('status', \App\Enum\TherapyStatus::IN_PROGRESS->value)->count()}}" :current="request()->routeIs('doctor.therapies.in_progress.index') || request()->routeIs('doctor.therapies.in_progress.chat') || request()->routeIs('doctor.therapies.in_progress.schedule')" wire:navigate>Berlangsung</flux:navlist.item>--}}
                            <flux:navlist.item :href="route('doctor.therapies.completed.index')" badge="{{auth()->user()->doctor->therapies->where('status', \App\Enum\TherapyStatus::COMPLETED->value)->count()}}">Riwayat</flux:navlist.item>
                        </flux:navlist.group>
                    @endcan
                    @can('isAdmin', auth()->user())
                        <flux:navlist.group expandable heading="Pengguna">
                            <flux:navlist.item :href="route('admin.users.doctor')" :current="request()->routeIs('admin.users.doctor')" wire:navigate badge="{{\App\Models\User::where('role', \App\Enum\UserRole::DOCTOR->value)->count()}}">Psikolog</flux:navlist.item>
                            <flux:navlist.item :href="route('admin.users.patient')" :current="request()->routeIs('admin.users.patient')" wire:navigate badge="{{\App\Models\User::where('role', \App\Enum\UserRole::PATIENT->value)->count()}}">Pasien</flux:navlist.item>
                        </flux:navlist.group>
                        <flux:navlist.group expandable heading="Pengaturan">
                            <flux:navlist.item :href="route('admin.settings.general')" :current="request()->routeIs('admin.settings.general')" wire:navigate badge="{{count(Illuminate\Support\Facades\Schema::getColumnListing('generals')) - 3}}">Umum</flux:navlist.item>
                            <flux:navlist.item :href="route('admin.settings.question')" :current="request()->routeIs('admin.settings.question')" wire:navigate badge="{{\App\Models\Question::count()}}">Pertanyaan</flux:navlist.item>
                        </flux:navlist.group>
                    @endcan
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
{{--                @if(auth()->user()->avatar)--}}
{{--                    <flux:profile--}}
{{--                        :name="auth()->user()->name"--}}
{{--                        avatar="{{asset('storage/' . auth()->user()->avatar)}}"--}}
{{--                        icon-trailing="chevrons-up-down"--}}
{{--                    />--}}
{{--                @else--}}
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevrons-up-down"
                    />
{{--                @endif--}}

                <flux:menu class="w-[220px]">
{{--                    <flux:menu.radio.group>--}}
{{--                        <div class="p-0 text-sm font-normal">--}}
{{--                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">--}}
{{--                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">--}}
{{--                                    <span--}}
{{--                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"--}}
{{--                                    >--}}
{{--                                        {{ auth()->user()->initials() }}--}}
{{--                                    </span>--}}
{{--                                </span>--}}

{{--                                <div class="grid flex-1 text-left text-sm leading-tight">--}}
{{--                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>--}}
{{--                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </flux:menu.radio.group>--}}

{{--                    <flux:menu.separator />--}}

                    @can('isDoctor', auth()->user())
                        <flux:menu.radio.group>
                            <flux:menu.item href="/settings/profile" icon="user" wire:navigate>{{ __('Profile') }}</flux:menu.item>
                        </flux:menu.radio.group>
                        <flux:menu.separator />
                    @endcan


                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

{{--            <flux:dropdown position="top" align="end">--}}
{{--                @if(auth()->user()->avatar)--}}
{{--                    <flux:profile--}}
{{--                        :name="auth()->user()->name"--}}
{{--                        avatar="{{asset('storage/' . auth()->user()->avatar)}}"--}}
{{--                        icon-trailing="chevrons-up-down"--}}
{{--                    />--}}
{{--                @else--}}
{{--                    <flux:profile--}}
{{--                        :name="auth()->user()->name"--}}
{{--                        :initials="auth()->user()->initials()"--}}
{{--                        icon-trailing="chevrons-up-down"--}}
{{--                    />--}}
{{--                @endif--}}

{{--                <flux:menu>--}}
{{--                    <flux:menu.radio.group>--}}
{{--                        <div class="p-0 text-sm font-normal">--}}
{{--                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">--}}
{{--                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">--}}
{{--                                    <span--}}
{{--                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"--}}
{{--                                    >--}}
{{--                                        {{ auth()->user()->initials() }}--}}
{{--                                    </span>--}}
{{--                                </span>--}}

{{--                                <div class="grid flex-1 text-left text-sm leading-tight">--}}
{{--                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>--}}
{{--                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </flux:menu.radio.group>--}}

{{--                    <flux:menu.separator />--}}

{{--                    @can('isDoctor', auth()->user())--}}
{{--                        <flux:menu.radio.group>--}}
{{--                            <flux:menu.item href="/settings/profile" icon="user" wire:navigate>{{ __('Profile') }}</flux:menu.item>--}}
{{--                        </flux:menu.radio.group>--}}
{{--                        <flux:menu.separator />--}}
{{--                    @endcan--}}

{{--                    <form method="POST" action="{{ route('logout') }}" class="w-full">--}}
{{--                        @csrf--}}
{{--                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">--}}
{{--                            {{ __('Log Out') }}--}}
{{--                        </flux:menu.item>--}}
{{--                    </form>--}}
{{--                </flux:menu>--}}
{{--            </flux:dropdown>--}}
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
