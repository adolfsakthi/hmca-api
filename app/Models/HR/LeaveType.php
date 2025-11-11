<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use SoftDeletes;

    protected $table = 'leave_types';

    protected $fillable = [
        'property_code',
        'name',
        'short_name',
        'yearly_limit',
        'carry_forward_limit',
        'consider_as',
        'description',
    ];

    public function leaves()
    {
        return $this->hasMany(\App\Models\HR\Leave::class, 'leave_type_id');
    }
}
