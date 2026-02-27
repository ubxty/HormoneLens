<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiabetesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avg_blood_sugar' => ['required', 'numeric', 'min:50', 'max:500'],
            'family_history' => ['required', 'boolean'],
            'frequent_urination' => ['required', 'in:often,occasionally,rarely'],
            'excessive_thirst' => ['required', 'in:often,occasionally,rarely'],
            'fatigue' => ['required', 'in:often,occasionally,rarely'],
            'blurred_vision' => ['required', 'in:often,occasionally,rarely'],
            'numbness_tingling' => ['required', 'boolean'],
            'slow_wound_healing' => ['required', 'boolean'],
            'unexplained_weight_loss' => ['required', 'boolean'],
            'sugar_cravings' => ['required', 'in:frequent,occasional,rare'],
            'energy_crashes_after_meals' => ['required', 'boolean'],
        ];
    }
}
