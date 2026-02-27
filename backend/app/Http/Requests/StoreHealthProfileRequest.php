<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHealthProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weight' => ['required', 'numeric', 'min:20', 'max:300'],
            'height' => ['required', 'numeric', 'min:50', 'max:250'],
            'avg_sleep_hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'stress_level' => ['required', 'in:low,medium,high'],
            'physical_activity' => ['required', 'in:sedentary,moderate,active'],
            'eating_habits' => ['nullable', 'string', 'max:1000'],
            'water_intake' => ['required', 'numeric', 'min:0', 'max:20'],
            'disease_type' => ['required', 'string', 'max:100'],
        ];
    }
}
