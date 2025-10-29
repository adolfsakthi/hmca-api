<?php

namespace App\Models\SuperAdmin;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [ 'property_name', 'property_address', 'user_email'];
    
}
