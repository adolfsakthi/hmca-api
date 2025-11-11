<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class LeaveApproval extends Model
{
    protected $table = 'leave_approvals';

    protected $fillable = [
        'property_code',
        'leave_id',
        'action',
        'performed_by',
        'performed_at',
        'remarks',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'performed_at' => 'datetime',
    ];

    public function leave()
    {
        return $this->belongsTo(\App\Models\HR\Leave::class, 'leave_id');
    }

    public function performer()
    {
        return $this->belongsTo(\App\Models\User::class, 'performed_by');
    }
}
