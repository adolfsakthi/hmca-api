<?php

namespace App\Models\HR\ESSL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;

    protected $table = 'essl_logs';

    protected $fillable = [
        'property_code','device_id','log_datetime','employee_code','verify_mode','in_out_mode','raw_payload'
    ];

    protected $casts = [
        'log_datetime' => 'datetime',
        'raw_payload' => 'array'
    ];

    public function device()
    {
        return $this->belongsTo(\App\Models\HR\ESSL\Device::class, 'device_id');
    }
}
