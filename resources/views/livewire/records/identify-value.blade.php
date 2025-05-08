<div class="relative rounded-lg px-6 py-4 bg-white border dark:bg-zinc-700 dark:border-transparent mb-5">
    <div class="overflow-x-auto">
        <table class="table-auto w-full text-sm border mb-2 mt-2">
            <thead>
            <tr class="text-center">
                <th class="border p-2">No</th>
                <th class="border p-2">Area</th>
                <th class="border p-2">{{ $datasetLabels[0] }}</th>
                <th class="border p-2">{{ $datasetLabels[2] }}</th>
                <th class="border p-2">{{ $datasetLabels[1] }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($labels as $index => $label)
                <tr class="text-left">
                    <td class="border p-2 text-center">{{ $loop->iteration }}</td>
                    <td class="border p-2">{{ $label }}</td>
                    <td class="border p-2 text-center">{{ $numberAnswers['Skala Kepentingan'][$index] }}</td>
                    <td class="border p-2 text-center">{{ $numberAnswers['Skor Kesesuaian'][$index] }}</td>
                    <td class="border p-2">
                        {{ $textAnswers[$datasetLabels[1]][$index] ?? '-' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

