<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'status' => 'sometimes|in:pending,approved_by_dept,rejected_by_dept,approved_by_hr,rejected_by_hr,cancelled',
            'approval_remarks' => 'nullable|string',
            'is_approved' => 'sometimes|boolean',
        ];
    }
}
