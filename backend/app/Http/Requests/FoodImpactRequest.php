<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FoodImpactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'food_item' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'string', 'max:100'],
        ];
    }
}
