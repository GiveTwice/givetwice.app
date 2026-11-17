<?php

namespace App\Http\Requests;

use App\Rules\PassesTurnstile;
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
        return [
            'list' => ['nullable', 'integer', Rule::exists('gift_list', 'list_id')->where('gift_id', $this->route('gift')->id)],
            'email' => [$this->user() && ! $this->isMethod('patch') ? 'nullable' : 'required', 'email', 'max:255'],
            'cf-turnstile-response' => Rule::when($this->needsTurnstile(), [new PassesTurnstile]),
        ];
    }

    public function messages(): array
    {
        return ['email.required' => __('Enter your email address.'), 'email.email' => __('Enter a valid email address.'), 'email.max' => __('Your email address is too long.')];
    }

    /**
     * Asks only a guest making a new reservation to prove they are human.
     *
     * A signed-in user already passed the check at sign-up. Changing the email needs a reservation this browser already made.
     */
    private function needsTurnstile(): bool
    {
        if ($this->user()) {
            return false;
        }

        if (! $this->isMethod('post')) {
            return false;
        }

        return filled(config('services.turnstile.secret_key'));
    }
}
