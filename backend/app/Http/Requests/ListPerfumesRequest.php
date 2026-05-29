<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListPerfumesRequest extends FormRequest
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
        $priceRules = ['nullable', 'integer', 'min:0', 'max:100000000'];
        $priceMaxRules = $priceRules;

        if ($this->filled('price_min')) {
            $priceMaxRules[] = 'gte:price_min';
        }

        return [
            'search' => ['nullable', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:120'],
            'aroma_category' => ['nullable', 'string', 'max:120'],
            'aroma_tag' => ['nullable', 'string', 'max:120'],
            'occasion' => ['nullable', 'string', 'max:120'],
            'price_min' => $priceRules,
            'price_max' => $priceMaxRules,
            'page' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
