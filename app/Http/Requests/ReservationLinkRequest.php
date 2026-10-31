<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReservationLinkRequest extends FormRequest
{
    public function rules(): array
    {
        return ['email' => ['required', 'email', 'max:255']];
    }

    public function messages(): array
    {
        return ['email.required' => __('Enter your email address.'), 'email.email' => __('Enter a valid email address.'), 'email.max' => __('Your email address is too long.')];
    }
}
