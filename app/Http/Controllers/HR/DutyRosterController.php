<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreDutyRosterRequest;
use App\Http\Requests\HR\UpdateDutyRosterRequest;
use App\Http\Requests\HR\UploadRosterRequest;
use App\Services\HR\DutyRosterService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *   name="HRMS Duty Roster",
 *   description="Duty roster management endpoints (weekly view, CRUD, bulk upload)"
 * )
 */
class DutyRosterController extends Controller
{
    protected DutyRosterService $service;

    public function __construct(DutyRosterService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/rosters",
     *   tags={"HRMS Duty Roster"},
     *   summary="Get weekly duty roster grouped by date and shift",
     *   description="Provide week_start (YYYY-MM-DD) to get roster for that week (7 days).",
     *   @OA\Parameter(name="week_start", in="query", required=false, @OA\Schema(type="string", format="date")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(
     *       @OA\Property(property="success", type="boolean", example=true),
     *       @OA\Property(property="data", type="object", description="Grouped roster by date->shift->employees")
     *     )
     *   )
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        $weekStart = $request->get('week_start', date('Y-m-d'));
        $rows = $this->service->listForWeek($propertyCode, $weekStart);

        $grouped = [];
        foreach ($rows as $r) {
            $d = $r->roster_date->format('Y-m-d');
            $shiftCode = $r->shift ? $r->shift->code : 'N/A';
            $shiftName = $r->shift ? $r->shift->name : null;
            $grouped[$d][$shiftCode]['shift'] = ['code' => $shiftCode, 'name' => $shiftName];
            $grouped[$d][$shiftCode]['employees'][] = [
                'id' => $r->employee->id,
                'employee_code' => $r->employee->employee_code,
                'name' => trim($r->employee->first_name . ' ' . $r->employee->last_name),
                'roster_id' => $r->id,
                'start_time' => $r->start_time ?? ($r->shift->start_time ?? null),
                'end_time' => $r->end_time ?? ($r->shift->end_time ?? null),
            ];
        }

        return response()->json(['success' => true, 'data' => $grouped]);
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/rosters/{id}",
     *   tags={"HRMS Duty Roster"},
     *   summary="Get a single roster entry",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(@OA\Property(property="data", type="object"))
     *   ),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $row = $this->service->get($propertyCode, $id);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'data' => $row]);
    }

    /**
     * @OA\Post(
     *   path="/api/hrms/rosters",
     *   tags={"HRMS Duty Roster"},
     *   summary="Create or upsert a duty roster entry for an employee on a date",
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(
     *       required={"roster_date"},
     *       @OA\Property(property="employee_code", type="string", description="Employee code (preferred)"),
     *       @OA\Property(property="employee_id", type="integer", description="Employee id (optional if employee_code provided)"),
     *       @OA\Property(property="roster_date", type="string", format="date"),
     *       @OA\Property(property="shift_id", type="integer", description="Shift id to assign"),
     *       @OA\Property(property="start_time", type="string", example="09:00"),
     *       @OA\Property(property="end_time", type="string", example="17:00"),
     *       @OA\Property(property="note", type="string")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Created"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreDutyRosterRequest $request)
    {
        $propertyCode = $request->get('property_code');
        $payload = $request->validated();
        $record = $this->service->store($propertyCode, $payload);
        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Employee not found for property or other error'], 422);
        }
        return response()->json(['success' => true, 'data' => $record], 201);
    }

    /**
     * @OA\Put(
     *   path="/api/hrms/rosters/{id}",
     *   tags={"HRMS Duty Roster"},
     *   summary="Update a roster entry",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(@OA\JsonContent(
     *       @OA\Property(property="shift_id", type="integer"),
     *       @OA\Property(property="start_time", type="string", example="09:00"),
     *       @OA\Property(property="end_time", type="string", example="17:00"),
     *       @OA\Property(property="note", type="string")
     *   )),
     *   @OA\Response(response=200, description="Updated"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function update(UpdateDutyRosterRequest $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $payload = $request->validated();
        $row = $this->service->update($propertyCode, $id, $payload);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'data' => $row], 200);
    }

    /**
     * @OA\Delete(
     *   path="/api/hrms/rosters/{id}",
     *   tags={"HRMS Duty Roster"},
     *   summary="Delete a roster entry",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="Deleted"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $ok = $this->service->delete($propertyCode, $id);
        if (!$ok) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'message' => 'Deleted'], 200);
    }

    /**
     * @OA\Post(
     *  path="/api/hrms/rosters/upload",
     *  tags={"HRMS Duty Roster"},
     *  summary="Upload roster file (Excel/CSV) and process",
     *  @OA\RequestBody(
     *    required=true,
     *    @OA\MediaType(mediaType="multipart/form-data",
     *      @OA\Schema(required={"file"}, @OA\Property(property="file", type="string", format="binary"))
     *    )
     *  ),
     *  @OA\Response(response=200, description="Processed")
     * )
     */
    public function upload(UploadRosterRequest $request)
    {
        $propertyCode = $request->get('property_code');
        $userId = auth()->id();
        $result = $this->service->processBulkUpload($propertyCode, $request->file('file'), $userId);
        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * @OA\Get(
     *   path="/api/hrms/rosters/sample",
     *   tags={"HRMS Duty Roster"},
     *   summary="Get sample roster format instructions",
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(@OA\Property(property="columns", type="array", @OA\Items(type="string")))
     *   )
     * )
     */
    public function sample()
    {
        return response()->json([
            'columns' => [
                'Emp. Code',
                'YYYY-MM-DD', // date columns...
            ],
            'note' => "First column must be employee code. Other columns' headers must be dates in YYYY-MM-DD format. Cells contain shift codes (e.g., M, A, N)."
        ]);
    }
}
