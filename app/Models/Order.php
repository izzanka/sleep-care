<?php

namespace App\Models;

use App\Enum\OrderStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => OrderStatus::class,
        ];
    }

    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    //    protected function createdAt(): Attribute
    //    {
    //        return Attribute::make(
    //            get: fn (string $value) => Carbon::createFromTimestamp($value)->format('d F Y H:i')
    //        );
    //    }
}
