<div>
    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="flex items-center space-x-2">
            <flux:icon.user></flux:icon.user>
            <flux:heading>
                Pasien
            </flux:heading>
        </div>

        <flux:separator class="mt-4 mb-4"></flux:separator>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <flux:heading>Nama</flux:heading>
                <flux:text>{{$therapy->patient->name}}</flux:text>
            </div>
            <div>
                <flux:heading>Email</flux:heading>
                <flux:text>{{$therapy->patient->email}}</flux:text>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <flux:heading>Usia</flux:heading>
                <flux:text>{{$therapy->patient->age}}</flux:text>
            </div>
            <div>
                <flux:heading>Jenis Kelamin</flux:heading>
                <flux:text>{{$therapy->patient->gender->label()}}</flux:text>
            </div>
        </div>
        <div class="mt-4">
            <flux:heading>Gangguan Lainnya</flux:heading>
            <flux:text>{{$problems}}</flux:text>
        </div>
    </div>
    <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
        <div class="flex items-center space-x-2">
            <flux:icon.star></flux:icon.star>
            <flux:heading>
                Ulasan
            </flux:heading>
        </div>

        <flux:separator class="mt-4 mb-4"></flux:separator>
        @if($therapy->doctor->ratings()->where('user_id', $therapy->patient_id)->value('rating'))
            @php
                $rating = $therapy->doctor->ratings()->where('user_id', $therapy->patient_id)->value('rating') ?? 0;
            @endphp

            <div class="flex">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= $rating)
                        <flux:icon.star variant="solid" class="text-yellow-400"></flux:icon.star>
                    @else
                        <flux:icon.star class="text-gray-300"></flux:icon.star>
                    @endif
                @endfor
            </div>
            <div class="mt-4 text-left">
                <flux:text>
                    {{$therapy->comment ?? '-'}}
                </flux:text>
            </div>
        @else
            <flux:heading>Belum ada ulasan</flux:heading>
        @endif

    </div>
</div>
