<?php

namespace App\Enums;

enum RoomStatusEnum: string
{
    case AVAILABLE = 'available';
    case BOOKED = 'booked';
    case OCCUPIED = 'occupied';
    case CLEANING = 'cleaning';
    case Dirty = 'dirty';
    case UNDER_MAINTENANCE = 'under_maintenance';
}
