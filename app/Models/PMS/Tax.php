<?php

namespace App\Models\PMS;

use App\Models\SuperAdmin\Property;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_code',
        'name',
        'value',
        'is_active',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_code', 'property_code');
    }

    public function calculateAmount(float $base): float
    {
        if (!$this->is_active || $this->value <= 0) {
            return 0;
        }

        return round(($base * $this->value) / 100, 2);
    }
}
