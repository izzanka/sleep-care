<div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
    <div class="overflow-x-auto">
        <table class="table-auto w-full text-sm mb-2 mt-2 rounded-lg border overflow-hidden">
            <thead class="bg-blue-400 dark:bg-blue-600 text-white">
            <tr class="text-center">
                <th class="p-2">No</th>
                <th class="p-2">Area</th>
                <th class="p-2">{{ $datasetLabels[0] }} (1-10)</th>
                <th class="p-2">{{ $datasetLabels[2] }} (1-10)</th>
                <th class="p-2">{{ $datasetLabels[1] }}</th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @foreach($labels as $index => $label)
                <tr class="text-left">
                    <td class="p-2 text-center">{{ $loop->iteration }}</td>
                    <td class="p-2">{{ $label }}</td>
                    <td class="p-2 text-center">{{ $numberAnswers['Skala Kepentingan'][$index] }}</td>
                    <td class="p-2 text-center">{{ $numberAnswers['Skor Kesesuaian'][$index] }}</td>
                    <td class="p-2">
                        {{ $textAnswers[$datasetLabels[1]][$index] ?? '-' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

