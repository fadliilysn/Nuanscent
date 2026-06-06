<?php

namespace Tests\Feature;

use App\Models\AromaCategory;
use App\Models\AromaTag;
use App\Models\Brand;
use App\Models\Occasion;
use App\Models\Perfume;
use App\Models\PerfumeVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommendations_return_only_published_perfumes_and_rank_better_matches_first(): void
    {
        [$brand, $fresh, $woody, $citrus, $smoky, $office, $date] = $this->createReferenceData();

        $strongMatch = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Strong Fresh Match',
            'slug' => 'strong-fresh-match',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'marketed_gender' => 'unisex',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $strongMatch->aromaTags()->attach($citrus);
        $strongMatch->occasions()->attach($office);

        $weakerMatch = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Smoky Date Match',
            'slug' => 'smoky-date-match',
            'price_min' => 550000,
            'price_max' => 650000,
            'intensity' => 'strong',
            'main_aroma_category_id' => $woody->id,
            'data_status' => 'published',
        ]);
        $weakerMatch->aromaTags()->attach($smoky);
        $weakerMatch->occasions()->attach($date);

        $draftMatch = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Hidden Fresh Draft',
            'slug' => 'hidden-fresh-draft',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'draft',
        ]);
        $draftMatch->aromaTags()->attach($citrus);
        $draftMatch->occasions()->attach($office);

        $response = $this->postJson('/api/recommendations', $this->validPayload([
            'avoided_tags' => ['smoky'],
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('recommendations.0.slug', 'strong-fresh-match')
            ->assertJsonMissing(['slug' => 'hidden-fresh-draft']);

        $recommendations = $response->json('recommendations');

        $this->assertGreaterThan($recommendations[1]['match_percentage'], $recommendations[0]['match_percentage']);
    }

    public function test_avoided_tags_reduce_score_and_return_explanation(): void
    {
        [$brand, $fresh, , $citrus, $smoky, $office] = $this->createReferenceData();

        $cleanPerfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Clean Fresh',
            'slug' => 'clean-fresh',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $cleanPerfume->aromaTags()->attach($citrus);
        $cleanPerfume->occasions()->attach($office);

        $avoidedPerfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Smoky Fresh',
            'slug' => 'smoky-fresh',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $avoidedPerfume->aromaTags()->attach([$citrus->id, $smoky->id]);
        $avoidedPerfume->occasions()->attach($office);

        $response = $this->postJson('/api/recommendations', $this->validPayload([
            'avoided_tags' => ['smoky'],
        ]))->assertOk();

        $recommendations = collect($response->json('recommendations'));
        $clean = $recommendations->firstWhere('slug', 'clean-fresh');
        $smokyResult = $recommendations->firstWhere('slug', 'smoky-fresh');

        $this->assertGreaterThan($smokyResult['match_percentage'], $clean['match_percentage']);
        $this->assertStringContainsString('menurunkan kecocokan', implode(' ', $smokyResult['matched_reasons']));
    }

    public function test_blind_buy_caution_label_and_reasons_are_returned(): void
    {
        [$brand, $fresh, , , $smoky, $office] = $this->createReferenceData();

        $perfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Bold Smoky',
            'slug' => 'bold-smoky',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'strong',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $perfume->aromaTags()->attach($smoky);
        $perfume->occasions()->attach($office);

        $this->postJson('/api/recommendations', $this->validPayload())
            ->assertOk()
            ->assertJsonPath('recommendations.0.blind_buy_caution.label', 'Sebaiknya Coba Sample Dulu')
            ->assertJsonCount(2, 'recommendations.0.blind_buy_caution.reasons');
    }

    public function test_supporting_aroma_tag_reasons_describe_nuance_for_selected_category(): void
    {
        [$brand, $fresh, , $citrus, , $office] = $this->createReferenceData();
        $aquatic = AromaTag::create([
            'name' => 'Aquatic',
            'slug' => 'aquatic',
        ]);

        $singleNuance = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Single Nuance',
            'slug' => 'single-nuance',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $singleNuance->aromaTags()->attach($citrus);
        $singleNuance->occasions()->attach($office);

        $multipleNuances = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Multiple Nuances',
            'slug' => 'multiple-nuances',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $multipleNuances->aromaTags()->attach([$citrus->id, $aquatic->id]);
        $multipleNuances->occasions()->attach($office);

        $response = $this->postJson('/api/recommendations', $this->validPayload())
            ->assertOk();

        $recommendations = collect($response->json('recommendations'));

        $this->assertContains(
            'Nuansa pendukung yang sejalan dengan pilihan Fresh: citrus.',
            $recommendations->firstWhere('slug', 'single-nuance')['matched_reasons'],
        );
        $this->assertContains(
            'Nuansa pendukung yang sejalan dengan pilihan Fresh: citrus dan aquatic.',
            $recommendations->firstWhere('slug', 'multiple-nuances')['matched_reasons'],
        );
    }

    public function test_supporting_aroma_tag_reasons_distinguish_selected_categories_from_nuance_tags(): void
    {
        [$brand, $fresh, , $citrus, , $office] = $this->createReferenceData();
        AromaCategory::create([
            'name' => 'Musky',
            'slug' => 'musky',
        ]);
        $aquatic = AromaTag::create([
            'name' => 'Aquatic',
            'slug' => 'aquatic',
        ]);
        $muskyTag = AromaTag::create([
            'name' => 'Musky',
            'slug' => 'musky',
        ]);

        $perfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Fresh Musky Nuance',
            'slug' => 'fresh-musky-nuance',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $perfume->aromaTags()->attach([$aquatic->id, $citrus->id, $muskyTag->id]);
        $perfume->occasions()->attach($office);

        $response = $this->postJson('/api/recommendations', $this->validPayload([
            'aroma_preference' => null,
            'aroma_preferences' => ['fresh', 'musky'],
        ]))->assertOk();

        $reasons = $response->json('recommendations.0.matched_reasons');
        $reasonText = implode(' ', $reasons);

        $this->assertContains('Sesuai dengan preferensi aroma Fresh.', $reasons);
        $this->assertContains('Nuansa pendukung yang sejalan dengan pilihan Fresh: citrus dan aquatic.', $reasons);
        $this->assertContains('Nuansa pendukung yang sejalan dengan pilihan Musky: musky.', $reasons);
        $this->assertStringNotContainsString('Tag aroma mendukung preferensimu', $reasonText);
        $this->assertStringNotContainsString('Sesuai dengan preferensi aroma Aquatic', $reasonText);
    }

    public function test_aroma_preferences_array_matches_any_selected_category(): void
    {
        [$brand, , $woody, , , $office] = $this->createReferenceData();
        $cedar = AromaTag::create([
            'name' => 'Cedar',
            'slug' => 'cedar',
        ]);

        $perfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Woody Multi Match',
            'slug' => 'woody-multi-match',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'main_aroma_category_id' => $woody->id,
            'data_status' => 'published',
        ]);
        $perfume->aromaTags()->attach($cedar);
        $perfume->occasions()->attach($office);

        $this->postJson('/api/recommendations', $this->validPayload([
            'aroma_preference' => null,
            'aroma_preferences' => ['fresh', 'woody'],
        ]))
            ->assertOk()
            ->assertJsonPath('recommendations.0.slug', 'woody-multi-match')
            ->assertJsonFragment(['Sesuai dengan preferensi aroma Woody.'])
            ->assertJsonFragment(['Nuansa pendukung yang sejalan dengan pilihan Woody: cedar.']);
    }

    public function test_one_two_and_three_aroma_preferences_are_supported(): void
    {
        [$brand, $fresh, , $citrus, , $office] = $this->createReferenceData();
        $clean = AromaCategory::create([
            'name' => 'Clean',
            'slug' => 'clean',
        ]);
        $musky = AromaCategory::create([
            'name' => 'Musky',
            'slug' => 'musky',
        ]);

        foreach ([
            [$fresh, $citrus, 'fresh-option', 'Fresh Option'],
            [$clean, AromaTag::create(['name' => 'Soapy', 'slug' => 'soapy']), 'clean-option', 'Clean Option'],
            [$musky, AromaTag::create(['name' => 'Musky', 'slug' => 'musky']), 'musky-option', 'Musky Option'],
        ] as [$category, $tag, $slug, $name]) {
            $perfume = Perfume::create([
                'brand_id' => $brand->id,
                'name' => $name,
                'slug' => $slug,
                'price_min' => 150000,
                'price_max' => 200000,
                'intensity' => 'soft',
                'main_aroma_category_id' => $category->id,
                'data_status' => 'published',
            ]);
            $perfume->aromaTags()->attach($tag);
            $perfume->occasions()->attach($office);
        }

        $this->postJson('/api/recommendations', $this->validPayload([
            'aroma_preference' => null,
            'aroma_preferences' => ['fresh'],
        ]))
            ->assertOk()
            ->assertJsonFragment(['slug' => 'fresh-option']);

        $this->postJson('/api/recommendations', $this->validPayload([
            'aroma_preference' => null,
            'aroma_preferences' => ['fresh', 'clean'],
        ]))
            ->assertOk()
            ->assertJsonFragment(['slug' => 'fresh-option'])
            ->assertJsonFragment(['slug' => 'clean-option']);

        $this->postJson('/api/recommendations', $this->validPayload([
            'aroma_preference' => null,
            'aroma_preferences' => ['fresh', 'clean', 'musky'],
        ]))
            ->assertOk()
            ->assertJsonFragment(['slug' => 'fresh-option'])
            ->assertJsonFragment(['slug' => 'clean-option'])
            ->assertJsonFragment(['slug' => 'musky-option']);
    }

    public function test_underrepresented_aroma_preferences_can_return_recommendations(): void
    {
        [$brand, , , , , $office] = $this->createReferenceData();

        foreach ([
            ['Soft', 'soft'],
            ['Musky', 'musky'],
            ['Sweet', 'sweet'],
            ['Powdery', 'powdery'],
        ] as [$name, $slug]) {
            $category = AromaCategory::create([
                'name' => $name,
                'slug' => $slug,
            ]);
            $tag = AromaTag::create([
                'name' => $name,
                'slug' => $slug,
            ]);
            $perfume = Perfume::create([
                'brand_id' => $brand->id,
                'name' => "{$name} Match",
                'slug' => "{$slug}-match",
                'price_min' => 150000,
                'price_max' => 200000,
                'intensity' => 'soft',
                'main_aroma_category_id' => $category->id,
                'data_status' => 'published',
            ]);
            $perfume->aromaTags()->attach($tag);
            $perfume->occasions()->attach($office);

            $this->postJson('/api/recommendations', $this->validPayload([
                'aroma_preference' => null,
                'aroma_preferences' => [$slug],
            ]))
                ->assertOk()
                ->assertJsonPath('recommendations.0.slug', "{$slug}-match");
        }
    }

    public function test_empty_avoided_tags_do_not_penalize_recommendations(): void
    {
        [$brand, $fresh, , $citrus, , $office] = $this->createReferenceData();

        $perfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'No Avoidance Penalty',
            'slug' => 'no-avoidance-penalty',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $perfume->aromaTags()->attach($citrus);
        $perfume->occasions()->attach($office);

        $withoutAvoidancePayload = $this->validPayload();
        unset($withoutAvoidancePayload['avoided_tags']);

        $withoutAvoidance = $this->postJson('/api/recommendations', $withoutAvoidancePayload)
            ->assertOk()
            ->json('recommendations.0');

        $withEmptyAvoidance = $this->postJson('/api/recommendations', $this->validPayload([
            'avoided_tags' => [],
        ]))
            ->assertOk()
            ->json('recommendations.0');

        $this->assertSame('no-avoidance-penalty', $withoutAvoidance['slug']);
        $this->assertSame($withoutAvoidance['match_percentage'], $withEmptyAvoidance['match_percentage']);
    }

    public function test_legacy_aroma_preference_alias_is_still_accepted(): void
    {
        [$brand, $fresh, , $citrus, , $office] = $this->createReferenceData();

        $perfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Fresh Alias Match',
            'slug' => 'fresh-alias-match',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $perfume->aromaTags()->attach($citrus);
        $perfume->occasions()->attach($office);

        $this->postJson('/api/recommendations', $this->validPayload([
            'aroma_preference' => 'fresh-clean',
        ]))
            ->assertOk()
            ->assertJsonPath('recommendations.0.slug', 'fresh-alias-match');
    }

    public function test_aroma_preferences_validation_rules(): void
    {
        $this->createReferenceData();

        $missingPayload = $this->validPayload(['aroma_preference' => null]);
        unset($missingPayload['aroma_preferences']);

        $this->postJson('/api/recommendations', $missingPayload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['aroma_preference', 'aroma_preferences']);

        $this->postJson('/api/recommendations', $this->validPayload([
            'aroma_preference' => null,
            'aroma_preferences' => [],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['aroma_preferences']);

        $this->postJson('/api/recommendations', $this->validPayload([
            'aroma_preference' => null,
            'aroma_preferences' => ['fresh', 'clean', 'woody', 'amber'],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['aroma_preferences']);

        $this->postJson('/api/recommendations', $this->validPayload([
            'aroma_preference' => null,
            'aroma_preferences' => ['tidak-ada'],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['aroma_preferences.0']);
    }

    public function test_recommendation_validation_rejects_invalid_inputs(): void
    {
        $this->createReferenceData();

        $this->postJson('/api/recommendations', [
            'occasion' => 'tidak-ada',
            'aroma_preference' => 'fresh',
            'price_min' => 300000,
            'price_max' => 100000,
            'intensity_preference' => 'loud',
            'avoided_tags' => ['tidak-ada'],
            'blind_buy_comfort' => 'pasti-aman',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'occasion',
                'price_max',
                'intensity_preference',
                'avoided_tags.0',
                'blind_buy_comfort',
            ]);
    }

    public function test_price_max_only_and_price_min_only_requests_are_valid(): void
    {
        [$brand, $fresh, , $citrus, , $office] = $this->createReferenceData();

        $perfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Budget Flexible',
            'slug' => 'budget-flexible',
            'price_min' => 150000,
            'price_max' => 200000,
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $perfume->aromaTags()->attach($citrus);
        $perfume->occasions()->attach($office);

        $this->postJson('/api/recommendations', $this->validPayload([
            'price_min' => null,
            'price_max' => 250000,
        ]))
            ->assertOk()
            ->assertJsonPath('recommendations.0.slug', 'budget-flexible')
            ->assertJsonFragment(['Masuk dalam rentang budget yang dipilih.']);

        $this->postJson('/api/recommendations', $this->validPayload([
            'price_min' => 100000,
            'price_max' => null,
        ]))
            ->assertOk()
            ->assertJsonPath('recommendations.0.slug', 'budget-flexible')
            ->assertJsonFragment(['Masuk dalam rentang budget yang dipilih.']);
    }

    public function test_recommendation_budget_uses_aggregate_prices_from_variants(): void
    {
        [$brand, $fresh, , $citrus, , $office] = $this->createReferenceData();

        $perfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Variant Budget',
            'slug' => 'variant-budget',
            'price_min' => 999000,
            'price_max' => 999000,
            'intensity' => 'soft',
            'main_aroma_category_id' => $fresh->id,
            'data_status' => 'published',
        ]);
        $perfume->aromaTags()->attach($citrus);
        $perfume->occasions()->attach($office);
        PerfumeVariant::create([
            'perfume_id' => $perfume->id,
            'volume_ml' => 50,
            'price' => 299000,
        ]);
        PerfumeVariant::create([
            'perfume_id' => $perfume->id,
            'volume_ml' => 100,
            'price' => 449000,
        ]);

        $this->postJson('/api/recommendations', $this->validPayload([
            'price_min' => null,
            'price_max' => 300000,
        ]))
            ->assertOk()
            ->assertJsonPath('recommendations.0.slug', 'variant-budget')
            ->assertJsonFragment(['Masuk dalam rentang budget yang dipilih.']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'occasion' => 'office-work',
            'aroma_preference' => 'fresh',
            'price_min' => 100000,
            'price_max' => 250000,
            'intensity_preference' => 'soft',
            'avoided_tags' => [],
            'blind_buy_comfort' => 'safe',
            'marketed_gender_preference' => 'no_preference',
        ], $overrides);
    }

    /**
     * @return array{Brand, AromaCategory, AromaCategory, AromaTag, AromaTag, Occasion, Occasion}
     */
    private function createReferenceData(): array
    {
        $brand = Brand::create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
        ]);
        $fresh = AromaCategory::create([
            'name' => 'Fresh',
            'slug' => 'fresh',
        ]);
        $woody = AromaCategory::create([
            'name' => 'Woody',
            'slug' => 'woody',
        ]);
        $citrus = AromaTag::create([
            'name' => 'Citrus',
            'slug' => 'citrus',
        ]);
        $smoky = AromaTag::create([
            'name' => 'Smoky',
            'slug' => 'smoky',
            'is_polarizing' => true,
        ]);
        $office = Occasion::create([
            'name' => 'Office / Work',
            'slug' => 'office-work',
        ]);
        $date = Occasion::create([
            'name' => 'Date',
            'slug' => 'date',
        ]);

        return [$brand, $fresh, $woody, $citrus, $smoky, $office, $date];
    }
}
