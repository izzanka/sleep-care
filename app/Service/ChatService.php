<?php

namespace App\Service;

use App\Models\Chat;

class ChatService
{
    public function get(int $patientId)
    {
        return Chat::where('receiver_id', $patientId)
            ->orWhere('sender_id', $patientId)
            ->orderBy('created_at')->get();
    }

    public function store(array $validated)
    {
        $validated['created_at'] = now();

        return Chat::create($validated);
    }
}
