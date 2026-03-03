<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHealthProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gender' => ['sometimes', 'in:female,male'],
            'weight' => ['sometimes', 'numeric', 'min:20', 'max:300'],
            'height' => ['sometimes', 'numeric', 'min:50', 'max:250'],
            'avg_sleep_hours' => ['sometimes', 'numeric', 'min:0', 'max:24'],
            'stress_level' => ['sometimes', 'in:low,medium,high'],
            'physical_activity' => ['sometimes', 'in:sedentary,moderate,active'],
            'eating_habits' => ['nullable', 'string', 'max:1000'],
            'water_intake' => ['sometimes', 'numeric', 'min:0', 'max:20'],
            'disease_type' => ['sometimes', 'string', 'max:100'],
        ];
    }
}
