<?php

namespace App\Models\SuperAdmin;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $fillable = [ 'property_name','property_Code','email','phone','address','city','state','zip_code','country' ,'description' ,'billing_active'];
}
