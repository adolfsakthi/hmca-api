<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@pms.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Super@123'),
                'role' => 'super_admin',
                'property_id' => null,
            ]
        );
    }
}
