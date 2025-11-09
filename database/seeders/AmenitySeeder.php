<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PMS\Amenity;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['property_code' => 'PROP001', 'name' => 'Free Wi-Fi', 'active' => true],
            ['property_code' => 'PROP001', 'name' => 'Air Conditioning', 'active' => true],
            ['property_code' => 'PROP001', 'name' => 'Room Service', 'active' => true],
            ['property_code' => 'PROP001', 'name' => 'Gym', 'active' => true],
            ['property_code' => 'PROP002', 'name' => 'Private Beach', 'active' => true],
            ['property_code' => 'PROP002', 'name' => 'Spa Access', 'active' => true],
            ['property_code' => 'PROP002', 'name' => 'Swimming Pool', 'active' => true],
        ];

        foreach ($amenities as $amenity) {
            Amenity::updateOrCreate(
                [
                    'name' => $amenity['name'],
                    'property_code' => $amenity['property_code'],
                ],
                ['active' => $amenity['active']]
            );
        }
    }
}
