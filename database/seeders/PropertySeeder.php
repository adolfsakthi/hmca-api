<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SuperAdmin\Property;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        Property::updateOrCreate(
            ['property_code' => 'H001'],
            [
                'property_name' => 'Hezee Downtown Hotel',
                'email' => 'downtown@hezee.com',
                'phone' => '+91-9876543210',
                'address' => '123 Mount Road',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'zip_code' => '600001',
                'country' => 'India',
                'description' => 'Modern business hotel in downtown Chennai.',
                'billing_active' => true,
            ]
        );

        Property::updateOrCreate(
            ['property_code' => 'H002'],
            [
                'property_name' => 'Hezee Beach Resort',
                'email' => 'beach@hezee.com',
                'phone' => '+91-9988776655',
                'address' => 'ECR Road, Mahabalipuram',
                'city' => 'Mahabalipuram',
                'state' => 'Tamil Nadu',
                'zip_code' => '603104',
                'country' => 'India',
                'description' => 'Luxury beach resort with sea-facing villas.',
                'billing_active' => true,
            ]
        );
    }
}
