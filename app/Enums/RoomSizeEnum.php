<?php

namespace App\Enums;

enum RoomSizeEnum: string
{
    case SINGLE = 'single';
    case DOUBLE = 'double';
    case TRIPAL = 'tripal';
    case KING = 'king';
    case QUEEN = 'queen';
    case QUAD = 'quad';
    case OTHERS = 'others';
}
