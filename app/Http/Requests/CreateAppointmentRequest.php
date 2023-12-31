<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool - Returns true if the user is authorized, false otherwise
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array - Array containing the validation rules
     */
    public function rules(): array
    {
        return [
            'service_id' => 'required|integer|exists:services,id',
            'appointment_start_time' => 'required|date_format:Y-m-d H:i:s',
            'users' => 'required|array',
            'users.*.first_name' => 'required|string',
            'users.*.last_name' => 'required|string',
            'users.*.email' => 'required|string|email',
        ];
    }
}
