<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGiftExchangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        if (! is_array($this->input('participants'))) {
            return;
        }

        $this->merge(['participants' => collect($this->input('participants', []))->map(function ($participant) {
            if (is_array($participant) && is_string($participant['email'] ?? null)) {
                $participant['email'] = strtolower(trim($participant['email']));
            }

            return $participant;
        })->all()]);
    }

    public function rules(): array
    {
        $minimum = $this->boolean('organizer_participates') ? 2 : 3;
        $maximum = $this->boolean('organizer_participates') ? 49 : 50;

        return [
            'name' => ['required', 'string', 'max:255'],
            'event_date' => ['nullable', 'date', 'after:today'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'budget_currency' => ['nullable', 'string', 'in:EUR,USD'],
            'participants' => ['required', 'array', "min:{$minimum}", "max:{$maximum}"],
            'participants.*' => ['array'],
            'participants.*.name' => ['required', 'string', 'max:255'],
            'participants.*.email' => ['nullable', 'email', 'max:255', 'distinct:ignore_case'],
            'organizer_participates' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['participants.max' => __('This group has reached its maximum number of participants.')];
    }
}
