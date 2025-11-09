<?php

namespace App\Repositories\HR;

use App\Models\HR\DutyRoster;
use App\Repositories\HR\Interfaces\DutyRosterRepositoryInterface;

class DutyRosterRepository implements DutyRosterRepositoryInterface
{
    public function listForWeek(string $propertyCode, string $weekStartDate)
    {
        // weekStartDate is YYYY-MM-DD (Monday). Return all roster rows for 7 days
        $start = $weekStartDate;
        $end = date('Y-m-d', strtotime("$start +6 days"));

        $rows = DutyRoster::with(['employee','shift'])
            ->where('property_code', $propertyCode)
            ->whereBetween('roster_date', [$start, $end])
            ->orderBy('roster_date')
            ->get();

        return $rows;
    }

    public function create(array $data)
    {
        return DutyRoster::create($data);
    }

    public function update(int $id, string $propertyCode, array $data)
    {
        $r = DutyRoster::where('property_code', $propertyCode)->find($id);
        if (!$r) return null;
        $r->update($data);
        return $r;
    }

    public function delete(int $id, string $propertyCode): bool
    {
        $r = DutyRoster::where('property_code', $propertyCode)->find($id);
        if (!$r) return false;
        $r->delete();
        return true;
    }

    public function upsertForEmployeeDate(string $propertyCode, int $employeeId, string $date, array $data)
    {
        // either create or update existing
        $record = DutyRoster::where('property_code', $propertyCode)
            ->where('employee_id', $employeeId)
            ->where('roster_date', $date)
            ->first();

        if ($record) {
            $record->update($data);
            return $record;
        }

        $data = array_merge($data, [
            'property_code' => $propertyCode,
            'employee_id' => $employeeId,
            'roster_date' => $date
        ]);

        return DutyRoster::create($data);
    }
}
