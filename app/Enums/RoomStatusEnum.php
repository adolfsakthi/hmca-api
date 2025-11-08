<?php

namespace App\Enums;

enum RoomStatusEnum: string
{
    case VACANT = 'vacant';
    case RESERVED = 'reserved';
    case OCCUPIED = 'occupied';
    case DIRTY = 'dirty';
    case CLEAN = 'clean';
    case MAINTENANCE = 'maintenance';
}
