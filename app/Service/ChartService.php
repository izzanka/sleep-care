<?php

namespace App\Service;

class ChartService
{
    public array $labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5', 'Minggu 6'];

    public function calculateMaxValue($data): int
    {
        return ceil($data->max() / 5) * 5;
    }
}
