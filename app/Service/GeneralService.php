<?php

namespace App\Service;

use App\Models\General;

class GeneralService
{
    public function get()
    {
        return General::first();
    }
}
