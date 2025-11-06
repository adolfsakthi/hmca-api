<?php

namespace App\Services\SuperAdmin;

use App\Repositories\SuperAdmin\Interfaces\ModuleRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ModuleService
{
    protected $moduleRepository;

    public function __construct(ModuleRepositoryInterface $moduleRepository)
    {
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * Get all modules
     */
    public function getAllModules()
    {
        $modules = $this->moduleRepository->getAllModules();

        return response()->json([
            'success' => true,
            'message' => 'Modules fetched successfully.',
            'data' => $modules
        ], 200);
    }

    /**
     * Get module by ID
     */
    public function getModuleById(int $id)
    {
        $module = $this->moduleRepository->findById($id);

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Module fetched successfully.',
            'data' => $module
        ], 200);
    }

    /**
     * Get all active modules
     */
    public function getActiveModules()
    {
        $modules = $this->moduleRepository->getActiveModules();

        return response()->json([
            'success' => true,
            'message' => 'Active modules fetched successfully.',
            'data' => $modules
        ], 200);
    }

    public function createModule(array $data)
    {
        $exists = $this->moduleRepository->findByCode($data['code']);
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'A module with this code already exists.'
            ], 409);
        }

        $module = $this->moduleRepository->create($data);

        return response()->json([
            'success' => true,
            'message' => 'Module created successfully.',
            'data' => $module
        ], 201);
    }


    public function updateModule(int $id, array $data)
    {

        $module = $this->moduleRepository->findById($id);
        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found.'
            ], 404);
        }

        if (isset($data['code'])) {
            $exists = $this->moduleRepository->findByCode($data['code']);
            if ($exists && $exists->id !== $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Another module with this code already exists.'
                ], 409);
            }
        }

        $updatedModule = $this->moduleRepository->update($module, $data);

        return response()->json([
            'success' => true,
            'message' => 'Module updated successfully.',
            'data' => $updatedModule
        ], 200);
    }

    
    public function deleteModule(int $id)
    {
        $module = $this->moduleRepository->findById($id);
        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found.'
            ], 404);
        }

        $deleted = $this->moduleRepository->delete($module);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted
                ? 'Module deleted successfully.'
                : 'Failed to delete module.'
        ], $deleted ? 200 : 500);
    }
}
