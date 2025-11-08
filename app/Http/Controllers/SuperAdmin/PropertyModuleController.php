<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\PropertyModuleService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Property Modules",
 *     description="Super Admin - Manage module assignments for properties"
 * )
 */
class PropertyModuleController extends Controller
{
    protected $propertyModuleService;

    public function __construct(PropertyModuleService $propertyModuleService)
    {
        $this->propertyModuleService = $propertyModuleService;
    }

    /**
     * @OA\Get(
     *     path="/api/properties/{id}/modules",
     *     summary="Get all modules for a property",
     *     tags={"Property Modules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Modules fetched successfully for property"
     *     )
     * )
     */
    public function index($propertyId)
    {
        return $this->propertyModuleService->getPropertyModules($propertyId);
    }

    /**
     * @OA\Post(
     *     path="/api/properties/{id}/modules",
     *     summary="Assign modules to a property",
     *     tags={"Property Modules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="modules", type="array",
     *                 @OA\Items(type="string", example="pms")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Modules assigned successfully")
     * )
     */
    public function assign(Request $request, $propertyId)
    {
        $validated = $request->validate([
            'modules' => 'required|array|min:1',
            'modules.*' => 'string|exists:modules,code'
        ]);

        return $this->propertyModuleService->assignModules($propertyId, $validated['modules']);
    }

    /**
     * @OA\Delete(
     *     path="/api/properties/{id}/modules",
     *     summary="Remove modules from a property",
     *     tags={"Property Modules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="modules", type="array",
     *                 @OA\Items(type="string", example="hrms")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Modules removed successfully")
     * )
     */
    public function remove(Request $request, $propertyId)
    {
        $validated = $request->validate([
            'modules' => 'required|array|min:1',
            'modules.*' => 'string|exists:modules,code'
        ]);

        return $this->propertyModuleService->removeModules($propertyId, $validated['modules']);
    }

    /**
     * @OA\Patch(
     *     path="/api/properties/{id}/modules/toggle",
     *     summary="Enable or disable a specific module for a property",
     *     tags={"Property Modules"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"module", "enabled"},
     *             @OA\Property(property="module", type="string", example="pms"),
     *             @OA\Property(property="enabled", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Module toggled successfully")
     * )
     */
    public function toggle(Request $request, $propertyId)
    {
        $validated = $request->validate([
            'module' => 'required|string|exists:modules,code',
            'enabled' => 'required|boolean'
        ]);

        return $this->propertyModuleService->toggleModule($propertyId, $validated['module'], $validated['enabled']);
    }
}
