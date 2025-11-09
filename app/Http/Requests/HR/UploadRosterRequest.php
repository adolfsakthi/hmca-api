<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UploadRosterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:51200'
        ];
    }
}
