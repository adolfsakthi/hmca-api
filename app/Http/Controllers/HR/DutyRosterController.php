<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreDutyRosterRequest;
use App\Http\Requests\HR\UpdateDutyRosterRequest;
use App\Http\Requests\HR\UploadRosterRequest;
use App\Services\HR\DutyRosterService;
use Illuminate\Http\Request;

/**
 * Duty Roster Controller
 */
class DutyRosterController extends Controller
{
    protected DutyRosterService $service;

    public function __construct(DutyRosterService $service)
    {
        $this->service = $service;
    }

    // Weekly view grouped for frontend
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

    public function show(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $row = $this->service->get($propertyCode, $id);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'data' => $row]);
    }

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

    public function update(UpdateDutyRosterRequest $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $payload = $request->validated();
        $row = $this->service->update($propertyCode, $id, $payload);
        if (!$row) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'data' => $row], 200);
    }

    public function destroy(Request $request, int $id)
    {
        $propertyCode = $request->get('property_code');
        $ok = $this->service->delete($propertyCode, $id);
        if (!$ok) return response()->json(['success' => false, 'message' => 'Not found'], 404);
        return response()->json(['success' => true, 'message' => 'Deleted'], 200);
    }

    public function upload(UploadRosterRequest $request)
    {
        $propertyCode = $request->get('property_code');
        $userId = auth()->id();
        $result = $this->service->processBulkUpload($propertyCode, $request->file('file'), $userId);
        return response()->json(['success' => true, 'data' => $result]);
    }

    // optional sample endpoint
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
