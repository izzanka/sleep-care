<div x-data="{ openIndex: null }">
    @foreach($sleepDiaries as $index => $sleepDiary)
        <div
            class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 mb-5 dark:border-transparent"
            x-ref="card{{ $index }}"
        >
            <div class="flex items-center w-full">
{{--                <flux:icon.calendar class="mr-2"/>--}}

                <flux:button
                    variant="ghost"
                    class="w-full"
                    @click="
                        openIndex = (openIndex === {{ $index }}) ? null : {{ $index }};
                        if (openIndex === {{ $index }}) {
                            $nextTick(() => {
                                const card = $refs['card{{ $index }}'];
                                const offset = 20;
                                const top = card.getBoundingClientRect().top + window.scrollY - offset;
                                window.scrollTo({ top, behavior: 'smooth' });
                            });
                        }
                    "
                >
                    <div class="flex items-center justify-between w-full">
                        Catatan Tidur {{ $dropdownLabels[$index - 1]}}
                        <svg xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke-width="1.5"
                             stroke="currentColor"
                             class="w-4 h-4 transition-transform duration-300"
                             :class="openIndex === {{ $index }} ? 'rotate-180' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </div>
                </flux:button>
            </div>

            <div x-show="openIndex === {{ $index }}" x-transition.duration.200ms class="mt-4">
                <div class="overflow-x-auto">
                    <table class="table-auto w-full text-sm border mb-2 mt-2">
                        <thead>
                        <tr>
                            <th class="border p-2 text-center">Hari</th>
                            @foreach($sleepDiary as $diary)
                                <th class="border p-2 text-center">{{$diary->date->translatedFormat('l')}}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <th class="border p-2 text-center">Tanggal</th>
                            @foreach($sleepDiary as $diary)
                                <th class="border p-2 text-center">{{ $diary->date->isoFormat('D MMMM') }}</th>
                            @endforeach
                        </tr>

                        <tr>
                            <td class="p-2 text-center font-bold" colspan="8">Siang Hari</td>
                        </tr>

                        @foreach($structuredQuestions as $question)
                            <tr>
                                <td class="border p-2 text-center font-bold">{{ $question->question }}</td>
                                @foreach($sleepDiary as $diary)
                                    @php
                                        $entry = $diary->questionAnswers->firstWhere('question_id', $question->id);
                                    @endphp
                                    <td class="border p-2">
                                        <div class="flex justify-center items-center h-full">
                                            @if($entry->answer->type == \App\Enum\QuestionType::BINARY->value)
                                                @if($entry->answer->answer)
                                                    <flux:icon.check-circle class="text-green-500"/>
                                                @else
                                                    <flux:icon.x-circle class="text-red-500"/>
                                                @endif
                                            @else
                                                {{ $entry->answer->answer ?? '-' }}
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>

                            @foreach($question->children as $child)
                                <tr>
                                    <td class="border p-2 text-left text-sm">{{ $child->question }}</td>
                                    @foreach($sleepDiary as $diary)
                                        @php
                                            $entry = $diary->questionAnswers->firstWhere('question_id', $child->id);
                                        @endphp
                                        <td class="border p-2 text-center">
                                            {{ $entry->answer->answer ?? '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            @if($question->id == 13)
                                <tr>
                                    <td class="p-2 text-center font-bold" colspan="8">Malam Hari</td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
