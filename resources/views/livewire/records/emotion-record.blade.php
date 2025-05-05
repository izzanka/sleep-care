<div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
    <div class="overflow-x-auto mt-4">
        <table class="min-w-[800px] w-full text-sm text-left">
            <thead>
            <tr>
                <th class="border p-3 text-center">No</th>
                @foreach($questions as $question)
                    <th class="border p-3 text-center">{{ $question }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($answerRows as $index => $row)
                <tr>
                    <td class="border p-3 text-center">{{ $index + 1 }}</td>
                    @foreach($questions as $question)
                        @php
                            $answerData = $row->firstWhere('question.question', $question)?->answer;
                            $type = $answerData?->type ?? null;
                            $value = $answerData?->answer ?? null;

                            $formattedValue = match($type) {
                                \App\Enum\QuestionType::DATE->value => $value ? \Carbon\Carbon::parse($value)->isoFormat('D MMMM') : '-',
                                \App\Enum\QuestionType::TIME->value, \App\Enum\QuestionType::NUMBER->value => $value ?? '-',
                                default => $value ?? '-',
                            };

                            $alignment = in_array($type, [\App\Enum\QuestionType::DATE->value, \App\Enum\QuestionType::TIME->value, \App\Enum\QuestionType::NUMBER->value]) ? 'text-center' : 'text-left';
                        @endphp
                        <td class="border p-3 {{ $alignment }}">
                            {{ $formattedValue }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
