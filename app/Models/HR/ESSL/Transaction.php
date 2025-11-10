<?php

namespace App\Models\HR\ESSL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'essl_transactions';

    protected $fillable = [
        'property_code',
        'device_id',
        'employee_code',
        'punch_at',
        'raw_line',
        'raw_payload',
        'processed',
        'processed_at',
    ];

    protected $casts = [
        'punch_at' => 'datetime',
        'raw_payload' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(\App\Models\HR\ESSL\Device::class, 'device_id');
    }
}
