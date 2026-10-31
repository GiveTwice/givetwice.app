<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GiftExchangeRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', $this->route('exchange'));
    }

    public function rules(): array
    {
        return [
            'giver_id' => ['required', Rule::exists('gift_exchange_participants', 'id')->where('exchange_id', $this->route('exchange')->id)],
            'receiver_id' => ['required', 'different:giver_id', Rule::exists('gift_exchange_participants', 'id')->where('exchange_id', $this->route('exchange')->id)],
            'mutual' => ['required', 'boolean'],
            'remove' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['receiver_id.different' => __('Pick two different people.')];
    }
}
