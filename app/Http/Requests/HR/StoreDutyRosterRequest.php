<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreDutyRosterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_code' => 'required_without:employee_id|string',
            'employee_id' => 'required_without:employee_code|integer',
            'roster_date' => 'required|date',
            'shift_id' => 'nullable|integer',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'note' => 'nullable|string',
        ];
    }
}
