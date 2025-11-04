<?php

namespace App\Models\PMS;

use App\Models\SuperAdmin\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'property_code',
        'active',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_code', 'property_Code');
    }
}
