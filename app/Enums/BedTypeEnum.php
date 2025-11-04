<?php

namespace App\Enums;

enum BedTypeEnum: string
{
    case KINGBED = 'kingbed';
    case QUEENBED = 'queenbed';
    case ELECTRICBED = 'electricbed';
    case FUTONBED = 'futonbed';
    case MATTRESSBED = 'mattressbed';
    case AIRBED = 'airbed';
}
