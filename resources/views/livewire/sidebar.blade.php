<div>
    <flux:navlist variant="outline">
        <flux:navlist.group class="grid">
            <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
            <flux:navlist.item icon="inbox" badge="{{ $unreadNotificationsCount }}" :href="route('notification')" wire:navigate :current="request()->routeIs('notification')">Notifikasi</flux:navlist.item>
            <flux:navlist.item icon="banknotes" :href="route('income')" :current="request()->routeIs('income')" wire:navigate>Pendapatan</flux:navlist.item>
            @can('isDoctor', $user)
                <flux:navlist.group expandable heading="Terapi">
                    <flux:navlist.group expandable heading="Berlangsung">
                        <flux:navlist.item :href="route('doctor.therapies.in_progress.index')" :current="request()->routeIs('doctor.therapies.in_progress.index')" wire:navigate>Halaman Utama</flux:navlist.item>
                        @if($hasOngoingTherapy)
                            <flux:navlist.item :href="route('doctor.therapies.in_progress.schedule')" :current="request()->routeIs('doctor.therapies.in_progress.schedule')" wire:navigate>Jadwal</flux:navlist.item>
                            <flux:navlist.item :href="route('doctor.therapies.in_progress.chat')" :current="request()->routeIs('doctor.therapies.in_progress.chat')" wire:navigate badge="{{$unreadChatsCount}}">Percakapan</flux:navlist.item>
                            <flux:navlist.group expandable heading="Catatan">
                                <flux:navlist.item :href="route('doctor.therapies.records.sleep_diary')" :current="request()->routeIs('doctor.therapies.records.sleep_diary')" wire:navigate badge="{{$unreadSleepDiary ? 'Baru' : ''}}">Tidur</flux:navlist.item>
                                <flux:navlist.item :href="route('doctor.therapies.records.identify_value')" :current="request()->routeIs('doctor.therapies.records.identify_value')" wire:navigate badge="{{$unreadIdentifyValue ? 'Baru' : ''}}">Nilai</flux:navlist.item>
                                <flux:navlist.item :href="route('doctor.therapies.records.thought_record')" :current="request()->routeIs('doctor.therapies.records.thought_record')" wire:navigate badge="{{$unreadThoughtRecord ? 'Baru' : ''}}">Pikiran</flux:navlist.item>
                                <flux:navlist.item :href="route('doctor.therapies.records.emotion_record')" :current="request()->routeIs('doctor.therapies.records.emotion_record')" wire:navigate badge="{{$unreadEmotionRecord ? 'Baru' : ''}}">Emosi</flux:navlist.item>
                                <flux:navlist.item :href="route('doctor.therapies.records.committed_action')" :current="request()->routeIs('doctor.therapies.records.committed_action')" wire:navigate badge="{{$unreadCommittedAction ? 'Baru' : ''}}">Tindakan</flux:navlist.item>
                            </flux:navlist.group>
                        @endif
                    </flux:navlist.group>
                    <flux:navlist.item :href="route('doctor.therapies.completed.index')" badge="{{ $completedTherapiesCount }}" :current="request()->routeIs('doctor.therapies.completed.index') || request()->routeIs('doctor.therapies.completed.detail')" wire:navigate>Riwayat</flux:navlist.item>
                </flux:navlist.group>
            @endcan
            @can('isAdmin', $user)
{{--                <flux:navlist.item icon="document" :href="route('admin.therapies')" :current="request()->routeIs('admin.therapies')" wire:navigate badge="{{ $allTherapyCount }}">Terapi</flux:navlist.item>--}}
                <flux:navlist.group expandable heading="Pengguna">
                    <flux:navlist.item :href="route('admin.users.doctor')" :current="request()->routeIs('admin.users.doctor')" wire:navigate badge="{{ $doctorCount }}">Psikolog</flux:navlist.item>
                    <flux:navlist.item :href="route('admin.users.patient')" :current="request()->routeIs('admin.users.patient')" wire:navigate badge="{{ $patientCount }}">Pasien</flux:navlist.item>
                </flux:navlist.group>
                <flux:navlist.group expandable heading="Pengaturan">
                    <flux:navlist.item :href="route('admin.settings.general')" :current="request()->routeIs('admin.settings.general')" wire:navigate badge="{{ $generalSettingCount }}">Umum</flux:navlist.item>
                    <flux:navlist.item :href="route('admin.settings.question')" :current="request()->routeIs('admin.settings.question')" wire:navigate badge="{{ $questionCount }}">Pertanyaan</flux:navlist.item>
                </flux:navlist.group>
            @endcan
        </flux:navlist.group>
    </flux:navlist>
</div>
