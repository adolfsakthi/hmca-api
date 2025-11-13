<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreEmployeeRequest;
use App\Http\Requests\HR\UpdateEmployeeRequest;
use App\Services\HR\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *   name="HRMS Employees",
 *   description="HRMS Employee management (Add, Bulk Upload, List, Edit, Delete)"
 * )
 */
class EmployeeController extends Controller
{
    protected EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/employees",
     *   tags={"HRMS Employees"},
     *   summary="List employees for the logged-in property",
     *   @OA\Parameter(name="q", in="query", description="Search text", @OA\Schema(type="string")),
     *   @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $propertyCode = $request->get('property_code');
        $search = $request->get('q');
        $perPage = (int) $request->get('per_page', 100);

        $employees = $this->employeeService->list($propertyCode, $search, $perPage);

        return response()->json($employees, 200);
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/employees/{id}",
     *   tags={"HRMS Employees"},
     *   summary="Get employee details",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $propertyCode = $request->get('property_code');
        $emp = $this->employeeService->get($propertyCode, $id);

        if (!$emp) {
            return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $emp], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/hrms/employees",
     *   tags={"HRMS Employees"},
     *   summary="Add new employee",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"first_name","employee_code"},
     *       @OA\Property(property="first_name", type="string", example="John"),
     *       @OA\Property(property="last_name", type="string", example="Doe"),
     *       @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *       @OA\Property(property="employee_code", type="string", example="EMP001"),
     *       @OA\Property(property="department", type="string", example="Front Office"),
     *       @OA\Property(property="designation", type="string", example="Senior Engineer"),
     *       @OA\Property(property="shift_start_time", type="string", example="09:00"),
     *       @OA\Property(property="shift_end_time", type="string", example="17:00"),
     *       @OA\Property(property="date_of_joining", type="string", format="date", example="2023-01-15"),
     *       @OA\Property(property="outlet", type="string", example="Main Building")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Created"),
     *   @OA\Response(response=422, description="Validation / duplicate code error")
     * )
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $propertyCode = $request->get('property_code');

        try {
            $employee = $this->employeeService->create(
                $propertyCode,
                $request->validated()
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $employee
        ], 201);
    }

    /**
     * @OA\Put(
     *   path="/api/hrms/employees/{id}",
     *   tags={"HRMS Employees"},
     *   summary="Update an employee",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(@OA\JsonContent()),
     *   @OA\Response(response=200, description="Updated"),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=422, description="Validation / duplicate code error")
     * )
     */
    public function update(UpdateEmployeeRequest $request, int $id): JsonResponse
    {
        $propertyCode = $request->get('property_code');

        try {
            $emp = $this->employeeService->update(
                $propertyCode,
                $id,
                $request->validated()
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }

        if (!$emp) {
            return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $emp], 200);
    }

    /**
     * @OA\Delete(
     *   path="/api/hrms/employees/{id}",
     *   tags={"HRMS Employees"},
     *   summary="Delete employee",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Deleted"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $propertyCode = $request->get('property_code');

        $ok = $this->employeeService->delete($propertyCode, $id);

        if (!$ok) {
            return response()->json(['success' => false, 'message' => 'Employee not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Deleted'], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/hrms/employees/upload",
     *   tags={"HRMS Employees"},
     *   summary="Bulk employee upload (Excel/CSV)",
     *   description="Accepts a file and returns info. Actual row processing can be added.",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         required={"file"},
     *         @OA\Property(
     *           property="file",
     *           type="string",
     *           format="binary"
     *         )
     *       )
     *     )
     *   ),
     *   @OA\Response(response=200, description="File accepted"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:51200',
        ]);

        $propertyCode = $request->get('property_code');

        $result = $this->employeeService->handleBulkUpload(
            $propertyCode,
            $request->file('file')
        );

        return response()->json([
            'success' => true,
            'data' => $result
        ], 200);
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/employees/sample/download",
     *   tags={"HRMS Employees"},
     *   summary="Download sample Excel format",
     *   @OA\Response(response=200, description="Sample info")
     * )
     */
    public function downloadSample(): JsonResponse
    {
        // You can point frontend to a static file.
        // For now just return column instructions.
        return response()->json([
            'success' => true,
            'columns' => [
                'First Name',
                'Last Name',
                'Email',
                'Department',
                'Employee Code',
                'Shift Start Time',
                'Shift End Time',
                'DOJ',
                'Designation',
                'Outlet'
            ],
            'note' => 'Upload sheet must include property employees only.'
        ]);
    }
}
