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
                <flux:heading>Gender</flux:heading>
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
        <div class="flex">
            <flux:icon.star variant="solid"></flux:icon.star>
            <flux:icon.star variant="solid"></flux:icon.star>
            <flux:icon.star variant="solid"></flux:icon.star>
            <flux:icon.star variant="solid"></flux:icon.star>
            <flux:icon.star></flux:icon.star>
        </div>
        <div class="mt-4 text-left">
            <flux:text>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deleniti dignissimos dolorem eius magni nisi provident sapiente vel velit. A alias animi deserunt, esse et non sequi soluta unde voluptas voluptate!
            </flux:text>
        </div>
    </div>
</div>
