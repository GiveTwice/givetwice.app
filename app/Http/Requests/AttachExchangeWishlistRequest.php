<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachExchangeWishlistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'wishlist_id' => ['nullable', 'required_without:name', Rule::exists('lists', 'id')->where('creator_id', $this->user()->id)],
            'name' => ['nullable', 'required_without:wishlist_id', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return ['wishlist_id.exists' => __('Choose one of your own lists.')];
    }
}
