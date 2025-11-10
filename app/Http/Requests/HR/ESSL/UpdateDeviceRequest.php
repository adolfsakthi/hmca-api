<?php

namespace App\Http\Requests\HR\ESSL;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'device_name' => 'sometimes|required|string|max:150',
            'serial_number' => 'sometimes|nullable|string|max:150',
            'ip_address' => 'sometimes|required|string|max:200',
            'port' => 'sometimes|nullable|integer',
            'username' => 'sometimes|nullable|string|max:100',
            'password' => 'sometimes|nullable|string',
            'location' => 'sometimes|nullable|string|max:150',
        ];
    }
}
