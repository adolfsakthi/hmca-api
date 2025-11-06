<?php

namespace App\Repositories\SuperAdmin\Interfaces;

interface PropertyModuleRepositoryInterface
{
    public function assignModulesToProperty(int $propertyId, array $moduleCodes);
    public function getModulesByProperty(int $propertyId);
    public function removeModulesFromProperty(int $propertyId, array $moduleCodes);
    public function toggleModuleStatus(int $propertyId, string $moduleCode, bool $enabled);
}
