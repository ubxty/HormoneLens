<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePcodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cycle_regularity' => ['required', 'in:regular,irregular,missed'],
            'avg_cycle_length_days' => ['nullable', 'integer', 'min:15', 'max:90'],
            'excess_facial_body_hair' => ['required', 'boolean'],
            'acne_oily_skin' => ['required', 'boolean'],
            'hair_thinning' => ['required', 'boolean'],
            'weight_gain_difficulty_losing' => ['required', 'boolean'],
            'mood_swings_anxiety' => ['required', 'boolean'],
            'dark_skin_patches' => ['required', 'boolean'],
            'fatigue_frequency' => ['required', 'in:often,occasionally,rarely'],
            'sleep_disturbances' => ['required', 'in:often,occasionally,rarely'],
            'sugar_cravings' => ['required', 'in:frequent,occasional,rare'],
            'insulin_resistance_diagnosed' => ['required', 'boolean'],
        ];
    }
}
