<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DutyRoster extends Model
{
    use SoftDeletes;

    protected $table = 'duty_rosters';

    protected $fillable = [
        'property_code',
        'employee_id',
        'shift_id',
        'roster_date',
        'start_time',
        'end_time',
        'note',
    ];

    protected $casts = [
        'roster_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\HR\Employee::class, 'employee_id');
    }

    public function shift()
    {
        return $this->belongsTo(\App\Models\HR\Shift::class, 'shift_id');
    }
}
