<?php

namespace App\Enum;

enum UserRole: int
{
    case ADMIN = 1;
    case DOCTOR = 2;
    case PATIENT = 3;
}
