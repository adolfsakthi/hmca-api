<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // middleware handles auth
    }

    public function rules(): array
    {
        return [
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'nullable|string|max:100',
            'email'             => 'nullable|email|max:255',
            'employee_code'     => 'required|string|max:50',
            'department'        => 'nullable|string|max:100',
            'designation'       => 'nullable|string|max:150',
            'shift_start_time'  => 'nullable|date_format:H:i',
            'shift_end_time'    => 'nullable|date_format:H:i',
            'date_of_joining'   => 'nullable|date',
            'outlet'            => 'nullable|string|max:100',
        ];
    }
}
