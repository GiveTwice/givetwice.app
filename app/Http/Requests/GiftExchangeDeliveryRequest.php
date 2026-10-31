<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GiftExchangeDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewStatus', $this->route('exchange'));
    }

    public function rules(): array
    {
        return ['action' => ['required', Rule::in(['whatsapp', 'sent', 'regenerate'])]];
    }

    public function messages(): array
    {
        return ['action.in' => __('Choose a delivery action.')];
    }
}
