<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SuperAdmin\Property;
use App\Models\SuperAdmin\Role;
use App\Models\SuperAdmin\Module;

class PropertyUserRoleSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * 🏨 1️⃣ Create Properties
         */
        $propertiesData = [
            [
                'property_code' => 'PROP001',
                'property_name' => 'Grand Hotel',
                'email' => 'grand@property.com',
                'phone' => '9876543210',
                'address' => '12 Main Street',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'zip_code' => '600001',
                'country' => 'India',
                'description' => 'Luxury 5-star hotel.',
                'billing_active' => true,
            ],
            [
                'property_code' => 'PROP002',
                'property_name' => 'Lake View Resort',
                'email' => 'lakeview@property.com',
                'phone' => '9876543222',
                'address' => '45 Lake Road',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'zip_code' => '560001',
                'country' => 'India',
                'description' => 'Beautiful lakeside resort.',
                'billing_active' => true,
            ],
        ];

        foreach ($propertiesData as $data) {
            Property::updateOrCreate(['property_code' => $data['property_code']], $data);
        }

        $properties = Property::all();

        /**
         * ⚙️ 2️⃣ Create Global Modules
         */
        $modulesData = [
            ['code' => 'PMS', 'name' => 'Property Management System', 'description' => 'Manage rooms, bookings, guests', 'status' => 'active'],
            ['code' => 'POS', 'name' => 'Point of Sale', 'description' => 'Handle restaurant and bar billing', 'status' => 'active'],
            ['code' => 'HR', 'name' => 'HR & Payroll', 'description' => 'Manage staff and payroll data', 'status' => 'active'],
        ];

        foreach ($modulesData as $mod) {
            Module::updateOrCreate(['code' => $mod['code']], $mod);
        }

        // Attach all modules to all properties (enabled)
        foreach ($properties as $property) {
            $property->modules()->syncWithPivotValues(Module::pluck('id')->toArray(), ['enabled' => true]);
        }

        /**
         * 👑 3️⃣ Create Global Super Admin Role
         */
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'property_code' => null,
                'name' => 'Super Admin',
                'description' => 'Full system access across all properties.',
            ]
        );

        /**
         * 👥 4️⃣ Create Roles Per Property
         * Admin, Frontdesk, HR
         */
        foreach ($properties as $property) {
            Role::updateOrCreate(
                ['slug' => 'admin', 'property_code' => $property->property_code],
                ['name' => 'Admin', 'description' => 'Property admin with full access.']
            );

            Role::updateOrCreate(
                ['slug' => 'frontdesk', 'property_code' => $property->property_code],
                ['name' => 'Frontdesk', 'description' => 'Handles guest check-ins and front desk operations.']
            );

            Role::updateOrCreate(
                ['slug' => 'hr', 'property_code' => $property->property_code],
                ['name' => 'HR', 'description' => 'Manages staff, attendance, and payroll.']
            );
        }

        /**
         * 👤 5️⃣ Create Users
         */

        // Global Super Admin
        User::updateOrCreate(
            ['email' => 'superadmin@property.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuperAdmin@123'),
                'role_id' => $superAdminRole->id,
                'property_code' => null,
            ]
        );

        foreach ($properties as $property) {
            // Admin
            $adminRole = Role::where('slug', 'admin')
                ->where('property_code', $property->property_code)
                ->first();

            // Frontdesk
            $frontdeskRole = Role::where('slug', 'frontdesk')
                ->where('property_code', $property->property_code)
                ->first();

            // HR
            $hrRole = Role::where('slug', 'hr')
                ->where('property_code', $property->property_code)
                ->first();

            // Create Admin user
            User::updateOrCreate(
                ['email' => $property->email],
                [
                    'name' => $property->property_name . ' Admin',
                    'password' => Hash::make('Property@123'),
                    'role_id' => $adminRole->id,
                    'property_code' => $property->property_code,
                ]
            );

            // Create Frontdesk user
            User::updateOrCreate(
                ['email' => strtolower($property->property_code) . '_frontdesk@property.com'],
                [
                    'name' => $property->property_name . ' Frontdesk',
                    'password' => Hash::make('Frontdesk@123'),
                    'role_id' => $frontdeskRole->id,
                    'property_code' => $property->property_code,
                ]
            );

            // Create HR user
            User::updateOrCreate(
                ['email' => strtolower($property->property_code) . '_hr@property.com'],
                [
                    'name' => $property->property_name . ' HR',
                    'password' => Hash::make('Hr@123'),
                    'role_id' => $hrRole->id,
                    'property_code' => $property->property_code,
                ]
            );
        }

        $this->command->info('✅ Properties, Roles (Admin, Frontdesk, HR), Modules, and Users seeded successfully!');
    }
}
