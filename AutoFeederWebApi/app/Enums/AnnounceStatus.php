<?php

namespace App\Enums;

enum AnnounceStatus: int
{
    case BOOT = 1;
    case DISPENSE = 2;
    case RECONNECT = 3;
    case COVER_IS_OPEN = 4;
    case LOW_FOOD = 5;
    case MANUAL_FEEDING = 6;
}
