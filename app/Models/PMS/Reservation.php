<?php

namespace App\Models\PMS;

use App\Models\SuperAdmin\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_code',
        'room_id',
        'guest_id',
        'check_in',
        'check_out',
        'arrival_from',
        'booking_type',
        'booking_reference_no',
        'purpose_of_visit',
        'remarks',
        'adults',
        'children',
        'source_of_booking',
        'booking_charge',
        'discount_percent',
        'discount_reason',
        'commission_percent',
        'commission_amount',
        'tax',
        'service_charge',
        'paid_amount',
        'total',
        'payment_mode',
        'advance_amount',
        'advance_remarks',
        'status',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'booking_charge' => 'float',
        'discount_percent' => 'float',
        'commission_percent' => 'float',
        'commission_amount' => 'float',
        'tax' => 'float',
        'service_charge' => 'float',
        'total' => 'float',
        'advance_amount' => 'float',
    ];

    // 🔗 Relationships
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_code', 'property_code');
    }

    // 🧮 Accessors
    public function getNightsAttribute()
    {
        return Carbon::parse($this->check_in)->diffInDays(Carbon::parse($this->check_out)) ?: 1;
    }

    public function getFormattedCheckInAttribute()
    {
        return $this->check_in ? $this->check_in->format('d M Y, h:i A') : null;
    }

    public function getFormattedCheckOutAttribute()
    {
        return $this->check_out ? $this->check_out->format('d M Y, h:i A') : null;
    }

    public function getGrandTotalAttribute()
    {
        return round($this->total + $this->tax + $this->service_charge, 2);
    }

    // 🔎 Scopes
    public function scopeForProperty($query, string $propertyCode)
    {
        return $query->where('property_code', $propertyCode);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'checked_in');
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('check_in', [$start, $end]);
    }
}
