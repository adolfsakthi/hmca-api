<?php

namespace App\Models\SuperAdmin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'property_code'
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_code', 'property_code');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
