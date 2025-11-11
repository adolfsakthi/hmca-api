<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use SoftDeletes;

    protected $table = 'leaves';

    protected $fillable = [
        'property_code',
        'employee_id',
        'leave_type_id',
        'duration_unit',
        'from_date',
        'to_date',
        'remarks',
        'status',
        'dept_approved_by',
        'dept_approved_at',
        'dept_approval_remarks',
        'hr_approved_by',
        'hr_approved_at',
        'hr_approval_remarks',
        'is_approved',
        'processed_at',
    ];

    protected $dates = [
        'from_date', 'to_date', 'dept_approved_at', 'hr_approved_at', 'processed_at'
    ];

    public function type()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function employee()
    {
        return $this->belongsTo(\App\Models\HR\Employee::class, 'employee_id');
    }

    public function deptApprover()
    {
        return $this->belongsTo(\App\Models\User::class, 'dept_approved_by');
    }

    public function hrApprover()
    {
        return $this->belongsTo(\App\Models\User::class, 'hr_approved_by');
    }

    public function approvals()
    {
        return $this->hasMany(LeaveApproval::class, 'leave_id');
    }
}
