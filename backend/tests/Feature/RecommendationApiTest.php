<?php

namespace Tests\Feature;

use App\Models\AromaCategory;
use App\Models\AromaTag;
use App\Models\Brand;
use App\Models\Occasion;
use App\Models\Perfume;
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

    public function test_recommendation_validation_rejects_invalid_inputs(): void
    {
        $this->createReferenceData();

        $this->postJson('/api/recommendations', [
            'occasion' => 'tidak-ada',
            'aroma_preference' => 'fresh-clean',
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'occasion' => 'office-work',
            'aroma_preference' => 'fresh-clean',
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
            'name' => 'Fresh / Clean',
            'slug' => 'fresh-clean',
        ]);
        $woody = AromaCategory::create([
            'name' => 'Woody / Earthy',
            'slug' => 'woody-earthy',
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
