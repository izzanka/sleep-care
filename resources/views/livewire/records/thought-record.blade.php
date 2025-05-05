<div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
    <div class="overflow-x-auto">
        <table class="table-auto w-full text-sm mb-2 mt-2">
            <thead>
            <tr>
                <th class="border p-2 text-center">No</th>
                @foreach($thoughtRecordQuestions as $question)
                    <th class="border p-2 text-center">{{ $question }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($chunks as $index => $chunk)
                <tr>
                    <td class="border p-2 text-center">{{ $index + 1 }}</td>
                    @foreach($thoughtRecordQuestions as $header)
                        @php
                            $answer = $chunk->firstWhere('question.question', $header)->answer;
                            $value = $answer->answer;
                            $type = $answer->type;
                        @endphp
                        <td class="border p-2">
                            @if($type === \App\Enum\QuestionType::DATE->value)
                                <div class="text-center">
                                    {{ \Carbon\Carbon::parse($value)->isoFormat('D MMMM') }}
                                </div>
                            @elseif($type == \App\Enum\QuestionType::TIME->value)
                                <div class="text-center">
                                    {{$value ?? '-'}}
                                </div>
                            @else
                                <div class="text-left">
                                    @if(\Illuminate\Support\Str::isJson($value))
                                        @foreach(json_decode($value) as $txt)
                                            <div class="py-1">
                                                {{$txt}}
                                            </div>
                                        @endforeach
                                    @else
                                        {{ $value ?? '-' }}
                                    @endif
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
