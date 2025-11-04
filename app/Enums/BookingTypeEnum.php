<?php

namespace App\Enums;

enum BookingTypeEnum: string
{
    case Online = 'online';
    case WalkIn = 'walk-in';
    case Corporate = 'corporate';
}