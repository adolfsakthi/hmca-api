<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Services\HR\DutyRosterService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="HRMS Duty Roster", description="Duty roster management")
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
     *   summary="List roster rows for a week",
     *   @OA\Parameter(name="week_start", in="query", description="YYYY-MM-DD (week start, Mon)", @OA\Schema(type="string")),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function index(Request $request)
    {
        $propertyCode = $request->get('property_code');
        $weekStart = $request->get('week_start', date('Y-m-d')); // default to today; frontend should send Monday

        $rows = $this->service->listForWeek($propertyCode, $weekStart);

        // Shape for frontend weekly view: group by date -> shift -> [employees]
        $grouped = [];
        foreach ($rows as $r) {
            $d = $r->roster_date->format('Y-m-d');
            $shiftCode = $r->shift ? $r->shift->code : 'N/A';
            $shiftName = $r->shift ? $r->shift->name : null;
            $grouped[$d][$shiftCode]['shift'] = ['code' => $shiftCode, 'name' => $shiftName];
            $grouped[$d][$shiftCode]['employees'][] = [
                'id' => $r->employee->id,
                'employee_code' => $r->employee->employee_code,
                'name' => $r->employee->first_name.' '.$r->employee->last_name,
                'roster_id' => $r->id,
                'start_time' => $r->start_time ?? ($r->shift->start_time ?? null),
                'end_time' => $r->end_time ?? ($r->shift->end_time ?? null),
            ];
        }

        return response()->json(['success' => true, 'data' => $grouped]);
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
    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:51200']);

        $propertyCode = $request->get('property_code');
        $userId = auth()->id();

        $result = $this->service->processBulkUpload($propertyCode, $request->file('file'), $userId);

        return response()->json(['success' => true, 'data' => $result]);
    }

    // For single CRUD you can implement store/update/destroy calling service methods
}
