<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['list' => ['nullable', 'integer', Rule::exists('gift_list', 'list_id')->where('gift_id', $this->route('gift')->id)], 'email' => [$this->user() && ! $this->isMethod('patch') ? 'nullable' : 'required', 'email', 'max:255']];
    }

    public function messages(): array
    {
        return ['email.required' => __('Enter your email address.'), 'email.email' => __('Enter a valid email address.'), 'email.max' => __('Your email address is too long.')];
    }
}
