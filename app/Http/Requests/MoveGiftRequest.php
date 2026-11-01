<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveGiftRequest extends FormRequest
{
    public function rules(): array
    {
        return ['direction' => ['required', Rule::in(['up', 'down'])]];
    }

    public function messages(): array
    {
        return ['direction.in' => __('Choose up or down.')];
    }
}
