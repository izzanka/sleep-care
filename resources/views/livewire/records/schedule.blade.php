<div>
    @foreach($therapySchedules as $schedule)
        <div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 mb-5 dark:border-transparent"
             x-data="{openTab: null}" wire:key="{{$schedule->id}}">
            <div class="flex items-center justify-between flex-wrap gap-y-2">
                <div class="flex items-center gap-x-3">
                    <flux:icon.video-camera></flux:icon.video-camera>
                    <flux:heading size="lg">{{$schedule->title}}</flux:heading>
                    <flux:badge size="sm"
                                color="{{$schedule->is_completed ? 'green' : 'zink'}}">{{$schedule->is_completed ? 'Sudah Dilaksanakan' : 'Belum Dilaksanakan'}}</flux:badge>
                </div>
            </div>
            <div class="mt-5">
                @if($schedule->link)
                    <flux:input icon="link" value="{{$schedule->link}}" readonly copyable/>
                @else
                    <flux:input icon="link" value="-" disabled/>
                @endif
            </div>
            <div class="flex items-center gap-2 mt-4">
                <flux:icon.clock></flux:icon.clock>
                <flux:text>{{$schedule->date->isoFormat('D MMMM Y')}} ({{\Carbon\Carbon::parse($schedule->time)->format('H:i')}} - {{\Carbon\Carbon::parse($schedule->time)->addHour()->format('H:i')}})</flux:text>
            </div>
            <div class="mt-4">
                <flux:button.group>
                    <flux:button @click="openTab = openTab === 'desc' ? null : 'desc'">
                        Deskripsi
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="w-4 h-4 transition-transform duration-300"
                            :class="openTab == 'desc' ? 'rotate-180' : ''"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </flux:button>
                    @if($schedule->note)
                        <flux:button @click="openTab = openTab === 'note' ? null : 'note'">
                            Catatan
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="w-4 h-4 transition-transform duration-300"
                                :class="openTab == 'note' ? 'rotate-180' : ''"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </flux:button>
                    @endif
                </flux:button.group>
            </div>
            <div x-show="openTab === 'desc'" x-transition.duration.200ms class="mt-4">
                <flux:text>
                    Deskripsi:
                </flux:text>
                <ul class="list-disc list-inside mt-2">
                    @foreach(json_decode($schedule->description) as $description)
                        <flux:text>
                            <li>
                                {{$description}}
                            </li>
                        </flux:text>
                    @endforeach
                </ul>
            </div>
            <div x-show="openTab === 'note'" x-transition.duration.200ms class="mt-4">
                <flux:text>
                    Catatan:
                </flux:text>
                <flux:text class="mt-2">
                    {{$schedule->note}}
                </flux:text>
            </div>
        </div>
    @endforeach
</div>
