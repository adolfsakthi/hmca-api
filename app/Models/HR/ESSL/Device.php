<?php

namespace App\Models\HR\ESSL;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Device extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'essl_devices';

    protected $fillable = [
        'property_code','device_name','serial_number','ip_address','port','username','password','location','status','last_ping_at','last_sync_at','notes'
    ];

    protected $casts = [
        'last_ping_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    // password stored encrypted
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? encrypt($value) : null;
    }

    public function getPasswordAttribute($value)
    {
        return $value ? decrypt($value) : null;
    }

    public function logs()
    {
        return $this->hasMany(\App\Models\HR\ESSL\Log::class, 'device_id');
    }

    public function transactions()
    {
        return $this->hasMany(\App\Models\HR\ESSL\Transaction::class, 'device_id');
    }
}
