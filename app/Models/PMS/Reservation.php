<?php

namespace App\Models\PMS;

use App\Enums\BookingTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\PaymentModeEnum;
use App\Enums\ReservationStatusEnum;
use App\Models\SuperAdmin\Property;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Reservation extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'property_code',
        'room_id',
        'check_in',
        'check_out',
        'arrival_from',
        'booking_type',
        'booking_reference_no',
        'purpose_of_visit',
        'remarks',
        'room_id',
        'adults',
        'children',
        'country_code',
        'mobile_no',
        'title',
        'first_name',
        'last_name',
        'father_name',
        'gender',
        'occupation',
        'dob',
        'anniversary',
        'nationality',
        'is_vip',
        'contact_type',
        'email',
        'country',
        'state',
        'city',
        'zipcode',
        'address',
        'identity_type',
        'identity_no',
        'front_doc',
        'back_doc',
        'identity_comments',
        'guest_image',
        'discount_reason',
        'discount_percent',
        'commission_percent',
        'commission_amount',
        'payment_mode',
        'advance_amount',
        'advance_remarks',
        'booking_charge',
        'tax',
        'service_charge',
        'total',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    protected $casts = [
        'status' => ReservationStatusEnum::class,
        'booking_type' => BookingTypeEnum::class,
        'gender' => GenderEnum::class,
        'PaymontMode' => PaymentModeEnum::class,
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_code', 'property_Code');
    }
}
