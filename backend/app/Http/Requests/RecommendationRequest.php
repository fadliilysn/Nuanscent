<?php

namespace App\Http\Requests;

use App\Support\AromaCategoryCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $aromaPreferences = $this->input('aroma_preferences');

        if (is_array($aromaPreferences)) {
            $this->merge([
                'aroma_preferences' => array_values(array_unique(array_map(
                    fn ($value) => is_string($value) ? trim($value) : $value,
                    $aromaPreferences,
                ), SORT_REGULAR)),
            ]);

            return;
        }

        if ($this->filled('aroma_preference')) {
            $this->merge([
                'aroma_preferences' => [trim((string) $this->input('aroma_preference'))],
            ]);
        }
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
            'aroma_preference' => ['nullable', 'string', Rule::in(AromaCategoryCatalog::acceptedSlugs()), 'required_without:aroma_preferences'],
            'aroma_preferences' => ['nullable', 'array', 'min:1', 'max:3', 'required_without:aroma_preference'],
            'aroma_preferences.*' => ['string', Rule::in(AromaCategoryCatalog::acceptedSlugs())],
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
