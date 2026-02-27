<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunSimulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:meal,sleep,stress'],
            'description' => ['required', 'string', 'max:500'],
            'parameters' => ['nullable', 'array'],
            'parameters.sleep_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'parameters.stress_level' => ['nullable', 'in:low,medium,high'],
            'parameters.meal_description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
