<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderGiftsRequest extends FormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'gifts' => ['required', 'array', 'min:1', 'max:100'],
            'gifts.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
