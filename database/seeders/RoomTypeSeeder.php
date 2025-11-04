<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PMS\RoomType;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['property_code' => 'H001', 'name' => 'Deluxe King', 'active' => true],
            ['property_code' => 'H001', 'name' => 'Executive Suite', 'active' => true],
            ['property_code' => 'H002', 'name' => 'Ocean Villa', 'active' => true],
        ];

        foreach ($types as $t) {
            RoomType::updateOrCreate(
                ['name' => $t['name'], 'property_code' => $t['property_code']],
                ['active' => $t['active']]
            );
        }
    }
}
