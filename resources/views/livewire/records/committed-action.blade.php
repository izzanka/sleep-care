<div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
    <div class="overflow-x-auto border mt-4">
        <table class="min-w-[800px] table-auto w-full text-sm text-left">
            <thead>
            <tr>
                <th class="border p-3 text-center">No</th>
                @foreach($questions as $question)
                    <th class="border p-3 text-center whitespace-nowrap">{{ $question }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($rows as $index => $row)
                <tr>
                    <td class="border p-3 text-center">{{ $index + 1 }}</td>
                    @foreach($questions as $question)
                        @php
                            $answerData = $row->firstWhere('question.question', $question)?->answer;
                            $isBinary = $answerData?->type === \App\Enum\QuestionType::BINARY->value;
                            $value = $answerData?->answer ?? null;
                        @endphp
                        <td class="border p-3">
                            @if($isBinary)
                                <div class="flex justify-center items-center h-full">
                                    @if($value)
                                        <flux:icon.check-circle class="text-green-500 w-5 h-5"/>
                                    @else
                                        <flux:icon.x-circle class="text-red-500 w-5 h-5"/>
                                    @endif
                                </div>
                            @else
                                <div class="text-left">
                                    {{ $value ?? '-' }}
                                </div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
