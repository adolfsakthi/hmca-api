<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSPayment extends Model
{
    use HasFactory;
    protected $fillable = ['pos_sale_id','amount','payment_mode','txn_reference'];
}
