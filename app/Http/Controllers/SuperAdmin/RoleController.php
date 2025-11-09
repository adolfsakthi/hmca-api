<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\RoleService;

/**
 * @OA\Tag(
 *     name="Roles",
 *     description="Manage roles within a property context"
 * )
 */
class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Get all roles for a specific property",
     *     tags={"Roles"},
     *     @OA\Response(
     *         response=200,
     *         description="Roles retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Roles retrieved successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="property_code", type="string", example="PROP001"),
     *                 @OA\Property(property="name", type="string", example="Administrator"),
     *                 @OA\Property(property="slug", type="string", example="admin"),
     *                 @OA\Property(property="description", type="string", example="Full access to all modules")
     *             ))
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        return response()->json($this->roleService->listRoles($propertyCode));
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Create a new role for a property",
     *     tags={"Roles"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_code", "name", "slug"},
     *             @OA\Property(property="name", type="string", example="Front Desk Manager"),
     *             @OA\Property(property="slug", type="string", example="front-desk"),
     *             @OA\Property(property="description", type="string", example="Handles front desk operations")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role created successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=10),
     *                 @OA\Property(property="property_code", type="string", example="PROP001"),
     *                 @OA\Property(property="name", type="string", example="Front Desk Manager"),
     *                 @OA\Property(property="slug", type="string", example="front-desk")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'name' => 'required|string',
            'slug' => 'required|string|unique:roles,slug',
            'description' => 'nullable|string',
        ]);

        return response()->json($this->roleService->create($validated), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/roles/{id}",
     *     summary="Get a specific role by ID and property",
     *     tags={"Roles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="property_code", type="string", example="PROP001"),
     *                 @OA\Property(property="name", type="string", example="Manager"),
     *                 @OA\Property(property="slug", type="string", example="manager"),
     *                 @OA\Property(property="description", type="string", example="Manager role details")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Role not found for this property")
     * )
     */
    public function show(Request $request, $id)
    {
        $propertyCode = $request->get('property_code');
        return response()->json($this->roleService->show($id, $propertyCode));
    }

    /**
     * @OA\Put(
     *     path="/api/roles/{id}",
     *     summary="Update a role for a property",
     *     tags={"Roles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"property_code"},
     *             @OA\Property(property="name", type="string", example="Reception Manager"),
     *             @OA\Property(property="slug", type="string", example="reception"),
     *             @OA\Property(property="description", type="string", example="Updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="property_code", type="string", example="PROP001"),
     *                 @OA\Property(property="name", type="string", example="Reception Manager"),
     *                 @OA\Property(property="slug", type="string", example="reception")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Role not found for this property")
     * )
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'property_code' => 'required|string',
            'name' => 'sometimes|required|string',
            'slug' => 'sometimes|required|string|unique:roles,slug,' . $id,
            'description' => 'nullable|string',
        ]);

        return response()->json($this->roleService->update($id, $validated));
    }

    /**
     * @OA\Delete(
     *     path="/api/roles/{id}",
     *     summary="Delete a role for a property",
     *     tags={"Roles"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Role ID",
     *         @OA\Schema(type="integer", example=5)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role deleted successfully."),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Role not found for this property")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $propertyCode = $request->get('property_code');
        return response()->json($this->roleService->delete($id, $propertyCode));
    }
}
