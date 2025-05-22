<?php

namespace App\Enum;

enum OrderStatus: string
{
    case SUCCESS = 'success';
    case SETTLEMENT = 'settlement';
    case DENY = 'deny';
    case PENDING = 'pending';
    case CANCEL = 'cancel';
    case EXPIRE = 'expire';
    case FAILURE = 'failure';

    public function label(): string
    {
        return match ($this) {
            self::SUCCESS => 'Berhasil',
            self::EXPIRE => 'Kedaluwarsa',
            self::CANCEL => 'Dibatalkan',
            self::PENDING => 'Tertunda',
            self::SETTLEMENT => 'Penyelesaian',
            self::FAILURE => 'Gagal',
            self::DENY => 'Ditolak',
        };
    }
}
