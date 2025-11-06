<?php

namespace App\Services\SuperAdmin;

use App\Repositories\SuperAdmin\Interfaces\PropertyModuleRepositoryInterface;

class PropertyModuleService
{
    protected $repository;

    public function __construct(PropertyModuleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getPropertyModules(int $propertyId)
    {
        $modules = $this->repository->getModulesByProperty($propertyId);

        return response()->json([
            'success' => true,
            'message' => 'Modules fetched successfully for property.',
            'data' => $modules
        ], 200);
    }

    public function assignModules(int $propertyId, array $moduleCodes)
    {
        $modules = $this->repository->assignModulesToProperty($propertyId, $moduleCodes);

        return response()->json([
            'success' => true,
            'message' => 'Modules assigned successfully.',
            'data' => $modules
        ], 200);
    }

    public function removeModules(int $propertyId, array $moduleCodes)
    {
        $modules = $this->repository->removeModulesFromProperty($propertyId, $moduleCodes);

        return response()->json([
            'success' => true,
            'message' => 'Modules removed successfully.',
            'data' => $modules
        ], 200);
    }

    public function toggleModule(int $propertyId, string $moduleCode, bool $enabled)
    {
        $modules = $this->repository->toggleModuleStatus($propertyId, $moduleCode, $enabled);

        return response()->json([
            'success' => true,
            'message' => 'Module ' . ($enabled ? 'enabled' : 'disabled') . ' successfully.',
            'data' => $modules
        ], 200);
    }
}
