<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

    protected $table = 'shifts';

    protected $fillable = [
        'property_code',
        'code',
        'name',
        'start_time',
        'end_time',
        'notes',
    ];

    protected $casts = [
        // start_time and end_time are stored as time
    ];
}
