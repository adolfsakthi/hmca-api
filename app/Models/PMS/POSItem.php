<?php

namespace App\Models\PMS;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSItem extends Model
{
    use HasFactory;
    protected $fillable = ['property_code','name','description','price','category','is_active'];
}
