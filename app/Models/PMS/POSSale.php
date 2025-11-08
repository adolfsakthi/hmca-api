<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_code','invoice_no','reservation_id',
        'customer_name','customer_email','customer_phone',
        'subtotal','tax','discount','total','payment_mode','status','notes'
    ];

    public function items() { return $this->hasMany(POSSaleItem::class); }
    public function payments() { return $this->hasMany(POSPayment::class); }
    public function reservation() { return $this->belongsTo(Reservation::class); }
}
