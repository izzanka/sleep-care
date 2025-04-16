<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
