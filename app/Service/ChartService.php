<?php

namespace App\Service;

class ChartService
{
    public array $labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5', 'Minggu 6'];

    public function labeling($startDate)
    {
        return collect(range(1, 6))->map(function ($week) use ($startDate) {
            $weekStart = $startDate->addWeeks($week - 1);
            $weekEnd = $weekStart->addDays(6);

            return "Minggu ke-$week (".$weekStart->isoFormat('D MMMM').' - '.$weekEnd->isoFormat('D MMMM').')';
        })->toArray();
    }

    public function calculateMaxValue($data): int
    {
        $max = max($data);

        return ($max % 5 === 0) ? $max + 5 : ceil($max / 5) * 5;
    }
}
