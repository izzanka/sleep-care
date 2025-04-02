<?php

namespace App\Enum;

enum OrderStatus: string
{
    case SUCCESS = 'success';
    case EXPIRE = 'expire';
    case CANCEL = 'cancel';
    case PENDING = 'pending';
    case SETTLEMENT = 'settlement';
    case FAILURE = 'failure';

    public function label(): string
    {
        return match ($this) {
            self::SUCCESS => 'Berhasil',
            self::EXPIRE => 'Kadaluwarsa',
            self::CANCEL => 'Batal',
            self::PENDING => 'Tertunda',
            self::SETTLEMENT => 'Penyelesaian',
            self::FAILURE => 'Gagal'
        };
    }
}
