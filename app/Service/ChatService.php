<?php

namespace App\Service;

use App\Models\Chat;

class ChatService
{
    public function get(?int $therapyId = null, ?int $id = null, ?int $sender_id = null, ?int $receiver_id = null)
    {
        $query = Chat::query();

        if($therapyId){
            $query->where('therapy_id', $therapyId);
        }

        if($id){
            $query->where('id', $id);
        }

        if($sender_id){
            $query->where('sender_id', $sender_id);
        }

        if($receiver_id){
            $query->where('receiver_id', $receiver_id);
        }

        return $query->oldest()->get();
    }

    public function markAsRead(int $therapyId, int $receiverId)
    {
        return Chat::where('therapy_id', $therapyId)
            ->where('receiver_id', $receiverId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function store(array $validated)
    {
        return Chat::create($validated);
    }
}
