<?php

namespace App\Models\SuperAdmin;

use App\Models\PMS\Room;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = ['property_name', 'property_code', 'email', 'phone', 'address', 'city', 'state', 'zip_code', 'country', 'description', 'billing_active'];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'property_code', 'property_Code');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'property_module')
            ->withPivot('enabled')
            ->wherePivot('enabled', true)
            ->withTimestamps();
    }

    public function allModules()
    {
        return $this->belongsToMany(Module::class, 'property_module')
            ->withPivot('enabled');
    }
}
