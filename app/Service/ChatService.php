<?php

namespace App\Service;

use App\Models\Chat;

class ChatService
{
    public function get(int $therapyId)
    {
        return Chat::where('therapy_id', $therapyId)->oldest()->get();
    }

    public function store(array $validated)
    {
        return Chat::create($validated);
    }
}
