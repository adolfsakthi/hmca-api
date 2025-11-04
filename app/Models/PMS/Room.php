<?php

namespace App\Models\PMS;

use App\Enums\BedTypeEnum;
use App\Enums\RoomSizeEnum;
use App\Enums\RoomStatusEnum;
use Illuminate\Database\Eloquent\Model;
use App\Models\SuperAdmin\Property;

class Room extends Model
{
    protected $fillable = [
        'property_code',
        'room_type_id',
        'room_number',
        'capacity',
        'extra_capability',
        'room_price',
        'bed_charge',
        'room_size',
        'bed_number',
        'bed_type',
        'room_description',
        'reserve_condition',
        'is_active',
        'status'
    ];

    protected $casts = [
        'room_size' => RoomSizeEnum::class,
        'bed_type' => BedTypeEnum::class,
        'status' => RoomStatusEnum::class,
    ];

    protected $attributes = [
        'status' => 'available',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_code', 'property_Code');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities', 'room_id', 'amenity_id');
    }
}
