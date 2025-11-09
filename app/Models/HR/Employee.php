<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employees';

    protected $fillable = [
        'property_code',
        'first_name',
        'last_name',
        'email',
        'employee_code',
        'department',
        'designation',
        'shift_start_time',
        'shift_end_time',
        'date_of_joining',
        'outlet',
        'avatar',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'date_of_joining' => 'date',
    ];
}
