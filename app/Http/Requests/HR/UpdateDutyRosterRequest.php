<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDutyRosterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'shift_id' => 'sometimes|nullable|integer',
            'start_time' => 'sometimes|nullable|date_format:H:i',
            'end_time' => 'sometimes|nullable|date_format:H:i',
            'note' => 'sometimes|nullable|string',
        ];
    }
}
