<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\ModuleService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Modules",
 *     description="Super Admin module management"
 * )
 */
class ModuleController extends Controller
{
    protected $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * @OA\Get(
     *     path="/api/modules",
     *     summary="Get all modules",
     *     tags={"Modules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Modules fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Modules fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="pms"),
     *                     @OA\Property(property="name", type="string", example="Property Management System"),
     *                     @OA\Property(property="description", type="string", example="Manage properties and reservations"),
     *                     @OA\Property(property="status", type="string", example="active")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return $this->moduleService->getAllModules();
    }

    /**
     * @OA\Get(
     *     path="/api/modules/{id}",
     *     summary="Get module by ID",
     *     tags={"Modules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Module ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Module fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Module fetched successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="pms"),
     *                 @OA\Property(property="name", type="string", example="Property Management System"),
     *                 @OA\Property(property="description", type="string", example="Manage rooms & reservations"),
     *                 @OA\Property(property="status", type="string", example="active")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Module not found")
     * )
     */
    public function show($id)
    {
        return $this->moduleService->getModuleById($id);
    }

    /**
     * @OA\Get(
     *     path="/api/modules/active",
     *     summary="Get all active modules",
     *     tags={"Modules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Active modules fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Active modules fetched successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="code", type="string", example="pms"),
     *                     @OA\Property(property="name", type="string", example="Property Management System"),
     *                     @OA\Property(property="status", type="string", example="active")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function active()
    {
        return $this->moduleService->getActiveModules();
    }

    /**
     * @OA\Post(
     *     path="/api/modules",
     *     summary="Create a new module",
     *     tags={"Modules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code", "name"},
     *             @OA\Property(property="code", type="string", example="pms"),
     *             @OA\Property(property="name", type="string", example="Property Management System"),
     *             @OA\Property(property="description", type="string", example="Manages properties and reservations"),
     *             @OA\Property(property="status", type="string", enum={"active","inactive"}, example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Module created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Module created successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=3),
     *                 @OA\Property(property="code", type="string", example="pms"),
     *                 @OA\Property(property="name", type="string", example="Property Management System"),
     *                 @OA\Property(property="description", type="string", example="Manage properties"),
     *                 @OA\Property(property="status", type="string", example="active")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=409, description="Conflict - module code already exists")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:modules,code|max:50',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'in:active,inactive'
        ]);

        return $this->moduleService->createModule($validated);
    }

    /**
     * @OA\Put(
     *     path="/api/modules/{id}",
     *     summary="Update an existing module",
     *     tags={"Modules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Module ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="string", example="hrms"),
     *             @OA\Property(property="name", type="string", example="HR Management System"),
     *             @OA\Property(property="description", type="string", example="Manages employees"),
     *             @OA\Property(property="status", type="string", enum={"active","inactive"}, example="inactive")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Module updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Module updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="code", type="string", example="hrms"),
     *                 @OA\Property(property="name", type="string", example="HR Management System"),
     *                 @OA\Property(property="description", type="string", example="Manage employees"),
     *                 @OA\Property(property="status", type="string", example="inactive")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Module not found"),
     *     @OA\Response(response=409, description="Duplicate code conflict")
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:50',
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'in:active,inactive'
        ]);

        return $this->moduleService->updateModule($id, $validated);
    }

    /**
     * @OA\Delete(
     *     path="/api/modules/{id}",
     *     summary="Delete a module",
     *     tags={"Modules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Module ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Module deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Module deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Module not found")
     * )
     */
    public function destroy($id)
    {
        return $this->moduleService->deleteModule($id);
    }
}
