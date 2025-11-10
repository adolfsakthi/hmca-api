<?php

namespace App\Http\Requests\HR\ESSL;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_name' => 'required|string|max:150',
            'serial_number' => 'nullable|string|max:150',
            'ip_address' => 'required|string|max:200',
            'port' => 'nullable|integer',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string',
            'location' => 'nullable|string|max:150',
            'status' => 'required|string|max:150'
        ];
    }
}
