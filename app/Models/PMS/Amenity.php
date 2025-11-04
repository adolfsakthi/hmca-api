<?php

namespace App\Models\PMS;

use App\Models\SuperAdmin\Property;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $fillable = ['property_code', 'name', 'active'];


    public function property()
    {
        return $this->belongsTo(Property::class, 'property_code', 'property_Code');
    }
}
