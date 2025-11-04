<?php

namespace App\Enums;

enum ReservationStatusEnum: string
{
    case RESERVED = 'reserved';
    case PENDING = 'pending';
    case BOOKED = 'booked';
    case CHECKED_IN = 'checked_in';
    case CHECKED_OUT = 'checked_out';
    case CANCELLED = 'cancelled';
}
