<?php

namespace App\Models\PMS;

use App\Models\SuperAdmin\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_code',
        'title',
        'name',
        'father_name',
        'gender',
        'occupation',
        'dob',
        'anniversary',
        'nationality',
        'country_code',
        'mobile_no',
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
        'is_vip',
        'is_blacklisted',
    ];

    protected $casts = [
        'dob' => 'date',
        'anniversary' => 'date',
        'is_vip' => 'boolean',
        'is_blacklisted' => 'boolean',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_code', 'property_code');
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
