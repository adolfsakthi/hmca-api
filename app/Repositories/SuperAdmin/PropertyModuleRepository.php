<?php

namespace App\Repositories\SuperAdmin;

use App\Models\SuperAdmin\Module;
use App\Models\SuperAdmin\Property;
use App\Repositories\SuperAdmin\Interfaces\PropertyModuleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyModuleRepository implements PropertyModuleRepositoryInterface
{
    public function assignModulesToProperty(int $propertyId, array $moduleCodes)
    {
        $property = Property::findOrFail($propertyId);
        $moduleIds = Module::whereIn('code', $moduleCodes)->pluck('id')->toArray();
        $property->modules()->syncWithoutDetaching($moduleIds);
        return $property->modules()->get();
    }

    public function getModulesByProperty(int $propertyId)
    {
        $property = Property::with('modules')->findOrFail($propertyId);
        return $property->modules;
    }

    public function removeModulesFromProperty(int $propertyId, array $moduleCodes)
    {
        $property = Property::findOrFail($propertyId);
        $moduleIds = Module::whereIn('code', $moduleCodes)->pluck('id')->toArray();
        $property->modules()->detach($moduleIds);
        return $property->modules()->get();
    }

    public function toggleModuleStatus(int $propertyId, string $moduleCode, bool $enabled)
    {
        $property = Property::findOrFail($propertyId);
        $module = Module::where('code', $moduleCode)->firstOrFail();

        $exists = DB::table('property_module')
            ->where('property_id', $property->id)
            ->where('module_id', $module->id)
            ->exists();

        if ($exists) {
            DB::table('property_module')
                ->where('property_id', $property->id)
                ->where('module_id', $module->id)
                ->update([
                    'enabled' => (int) $enabled,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('property_module')->insert([
                'property_id' => $property->id,
                'module_id' => $module->id,
                'enabled' => (int) $enabled,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        return $property->modules()->get();
    }
}
