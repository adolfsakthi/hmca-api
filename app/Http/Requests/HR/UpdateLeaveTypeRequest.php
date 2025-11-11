<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:191',
            'short_name' => 'nullable|string|max:50',
            'yearly_limit' => 'nullable|integer|min:0',
            'carry_forward_limit' => 'nullable|integer|min:0',
            'consider_as' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ];
    }
}
