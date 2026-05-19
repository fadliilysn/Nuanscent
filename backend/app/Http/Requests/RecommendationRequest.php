<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $priceMaxRules = ['nullable', 'integer', 'min:0'];

        if ($this->filled('price_min')) {
            $priceMaxRules[] = 'gte:price_min';
        }

        return [
            'occasion' => ['required', 'string', Rule::exists('occasions', 'slug')],
            'aroma_preference' => ['required', 'string', Rule::exists('aroma_categories', 'slug')],
            'price_min' => ['nullable', 'integer', 'min:0'],
            'price_max' => $priceMaxRules,
            'intensity_preference' => ['nullable', 'string', Rule::in(['soft', 'medium', 'strong', 'no_preference'])],
            'avoided_tags' => ['nullable', 'array'],
            'avoided_tags.*' => ['string', 'distinct', Rule::exists('aroma_tags', 'slug')],
            'blind_buy_comfort' => ['required', 'string', Rule::in(['safe', 'flexible', 'adventurous'])],
            'marketed_gender_preference' => [
                'nullable',
                'string',
                Rule::in(['no_preference', 'unisex', 'pria', 'wanita', 'maskulin', 'feminin', 'male', 'female']),
            ],
        ];
    }
}
