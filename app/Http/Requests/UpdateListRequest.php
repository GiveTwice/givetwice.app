<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('list'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'occasion' => ['nullable', Rule::in(['sinterklaas', 'christmas', 'birthday', 'other'])],
            'occasion_date' => ['nullable', 'date_format:Y-m-d'],
            'spoiler_mode' => ['sometimes', 'boolean'],
            'spoiler_confirmation' => $this->boolean('spoiler_mode') && ! $this->route('list')->spoiler_confirmed_at ? ['required', 'accepted'] : ['sometimes', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'occasion.in' => __('Pick an occasion from the list.'),
            'occasion_date.date_format' => __('Pick a valid date.'),
            'spoiler_confirmation.required' => __('Confirm that you want to see reservations.'),
            'spoiler_confirmation.accepted' => __('Confirm that you want to see reservations.'),
        ];
    }
}
