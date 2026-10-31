<?php

namespace App\Http\Requests;

use App\Enums\SupportedCurrency;
use App\Models\Gift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGiftRequest extends FormRequest
{
    public function rules(): array
    {
        $gift = $this->route('gift');
        $giftListIds = $gift->lists()->pluck('lists.id');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'owner_note' => ['nullable', 'string', 'max:1500'],
            'shop_name' => ['nullable', 'string', 'max:255'],
            'tracks_edits' => ['nullable', 'boolean'],
            'edited_fields' => ['nullable', 'array'],
            'edited_fields.*' => ['string', Rule::in(['title', 'description', 'price_in_cents', 'currency', 'shop_name'])],
            'description' => ['nullable', 'string', 'max:1500'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'currency' => ['nullable', 'string', Rule::enum(SupportedCurrency::class)],
            'url' => [
                'nullable',
                'url',
                'max:2048',
                function ($attribute, $value, $fail) use ($gift, $giftListIds) {
                    if ($value === $gift->url) {
                        return;
                    }

                    $conflictExists = Gift::where('url', $value)
                        ->where('id', '!=', $gift->id)
                        ->whereHas('lists', fn ($q) => $q->whereIn('lists.id', $giftListIds))
                        ->exists();

                    if ($conflictExists) {
                        $fail(__("You've already added this gift to this list."));
                    }
                },
            ],
            'most_wanted' => ['sometimes', 'boolean'],
            'allow_multiple_claims' => ['nullable', 'boolean'],
        ];

    }

    public function messages(): array
    {
        return ['title.required' => __('Give your gift a name.')];
    }
}
