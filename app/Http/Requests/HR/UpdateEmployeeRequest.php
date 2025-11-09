<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'        => 'sometimes|required|string|max:100',
            'last_name'         => 'sometimes|nullable|string|max:100',
            'email'             => 'sometimes|nullable|email|max:255',
            'employee_code'     => 'sometimes|required|string|max:50',
            'department'        => 'sometimes|nullable|string|max:100',
            'designation'       => 'sometimes|nullable|string|max:150',
            'shift_start_time'  => 'sometimes|nullable|date_format:H:i',
            'shift_end_time'    => 'sometimes|nullable|date_format:H:i',
            'date_of_joining'   => 'sometimes|nullable|date',
            'outlet'            => 'sometimes|nullable|string|max:100',
        ];
    }
}
