<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SuperAdmin\Property;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ 1. Create a super admin user (global)
        User::updateOrCreate(
            ['email' => 'superadmin@hezee.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuperAdmin@123'),
                'role' => 'super_admin',
                'property_id' => null,
            ]
        );

        // ✅ 2. Loop through all properties and create a user for each
        $properties = Property::all();

        foreach ($properties as $property) {
            User::updateOrCreate(
                ['email' => $property->email],
                [
                    'name' => $property->property_name . ' Admin',
                    'password' => Hash::make('Property@123'),
                    'role' => 'property_admin',
                    'property_id' => $property->id,
                ]
            );
        }
    }
}
