<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FoodCompareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'food_a' => ['required', 'string', 'max:255'],
            'food_b' => ['required', 'string', 'max:255'],
            'meal_time' => ['nullable', 'string', 'in:morning,afternoon,evening,night'],
        ];
    }
}
