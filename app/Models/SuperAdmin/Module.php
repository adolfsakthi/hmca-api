<?php

namespace App\Models\SuperAdmin;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function properties()
    {
        return $this->belongsToMany(Property::class, 'property_module')
            ->withPivot('enabled')
            ->withTimestamps();
    }
}
