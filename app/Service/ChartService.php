<?php

namespace App\Service;

class ChartService
{
    public array $labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5', 'Minggu 6'];

    public function calculateMaxValue($data): int
    {
        $max = max($data);
        return ($max % 5 === 0) ? $max + 5 : ceil($max / 5) * 5;
    }
}
