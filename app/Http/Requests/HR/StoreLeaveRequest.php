<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'leave_type_id' => 'required|integer|exists:leave_types,id',
            'duration_unit' => 'required|in:full,3/4,half,1/4',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'remarks' => 'nullable|string',
            'is_approved' => 'boolean'
        ];
    }
}
