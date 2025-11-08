<?php

namespace App\Models\PMS;

use App\Models\PMS\RoomType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateType extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_code',
        'room_type_id',
        'name',
        'description',
        'base_price',
    ];

    public function room_type()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }
}
