<?php

namespace Tests\Feature;

use App\Models\AromaCategory;
use App\Models\AromaTag;
use App\Models\Brand;
use App\Models\Note;
use App\Models\Occasion;
use App\Models\Perfume;
use App\Models\PerfumeVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_perfume_catalog_lists_only_published_perfumes_with_filters(): void
    {
        [$brand, $category, $tag, $occasion] = $this->createReferenceData();

        $published = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Catalog Perfume',
            'slug' => 'catalog-perfume',
            'short_description' => 'Aroma ringan untuk harian.',
            'price_min' => 150000,
            'price_max' => 200000,
            'intensity' => 'soft',
            'marketed_gender' => 'unisex',
            'main_aroma_category_id' => $category->id,
            'data_status' => 'published',
        ]);
        $published->aromaTags()->attach($tag);
        $published->occasions()->attach($occasion);

        Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Draft Perfume',
            'slug' => 'draft-perfume',
            'main_aroma_category_id' => $category->id,
            'data_status' => 'draft',
        ]);

        $response = $this->getJson('/api/perfumes?brand=test-brand&aroma_category=fresh&aroma_tag=citrus&occasion=kantor&price_max=250000');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'catalog-perfume')
            ->assertJsonMissing(['slug' => 'draft-perfume'])
            ->assertJsonPath('data.0.brand.slug', 'test-brand')
            ->assertJsonPath('data.0.main_aroma_category.slug', 'fresh')
            ->assertJsonPath('meta.per_page', 12);

        $this->getJson('/api/perfumes?aroma_category=fresh-clean')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'catalog-perfume');
    }

    public function test_perfume_detail_exposes_relationships_and_rejects_drafts(): void
    {
        [$brand, $category, $tag, $occasion, $note] = $this->createReferenceData();

        $perfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Detail Perfume',
            'slug' => 'detail-perfume',
            'official_description' => 'Deskripsi resmi dari sumber.',
            'main_aroma_category_id' => $category->id,
            'source_name' => 'Official Site',
            'last_verified_at' => '2026-05-18',
            'data_status' => 'published',
        ]);
        $perfume->aromaTags()->attach($tag);
        $perfume->occasions()->attach($occasion);
        $perfume->notes()->attach($note, ['position' => 'top']);
        PerfumeVariant::create([
            'perfume_id' => $perfume->id,
            'label' => 'Botol 50 ml',
            'volume_ml' => 50,
            'price' => 299000,
        ]);
        PerfumeVariant::create([
            'perfume_id' => $perfume->id,
            'label' => 'Botol 100 ml',
            'volume_ml' => 100,
            'price' => 449000,
        ]);

        $draft = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Hidden Perfume',
            'slug' => 'hidden-perfume',
            'main_aroma_category_id' => $category->id,
            'data_status' => 'draft',
        ]);

        $this->getJson('/api/perfumes/'.$perfume->slug)
            ->assertOk()
            ->assertJsonPath('data.official_description', 'Deskripsi resmi dari sumber.')
            ->assertJsonPath('data.price_min', 299000)
            ->assertJsonPath('data.price_max', 449000)
            ->assertJsonPath('data.variants.0.label', 'Botol 50 ml')
            ->assertJsonPath('data.variants.1.volume_ml', 100)
            ->assertJsonPath('data.notes.0.position', 'top')
            ->assertJsonPath('data.source.last_verified_at', '2026-05-18');

        $this->getJson('/api/perfumes/'.$draft->slug)->assertNotFound();
    }

    public function test_variant_prices_refresh_parent_range_and_catalog_filters_use_aggregate_prices(): void
    {
        [$brand, $category] = $this->createReferenceData();

        $perfume = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Variant Price',
            'slug' => 'variant-price',
            'price_min' => 999000,
            'price_max' => 999000,
            'main_aroma_category_id' => $category->id,
            'data_status' => 'published',
        ]);

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

        $perfume->refresh();

        $this->assertSame(299000, $perfume->price_min);
        $this->assertSame(449000, $perfume->price_max);

        $this->getJson('/api/perfumes?price_max=300000')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'variant-price']);

        $this->getJson('/api/perfumes?price_max=250000')
            ->assertOk()
            ->assertJsonMissing(['slug' => 'variant-price']);
    }

    public function test_brand_detail_and_reference_endpoints_return_public_data(): void
    {
        [$brand, $category, $tag, $occasion, $note] = $this->createReferenceData();

        $published = Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Published Count',
            'slug' => 'published-count',
            'main_aroma_category_id' => $category->id,
            'data_status' => 'published',
        ]);
        $published->aromaTags()->attach($tag);
        $published->occasions()->attach($occasion);
        $published->notes()->attach($note, ['position' => 'base']);

        Perfume::create([
            'brand_id' => $brand->id,
            'name' => 'Draft Count',
            'slug' => 'draft-count',
            'main_aroma_category_id' => $category->id,
            'data_status' => 'draft',
        ]);

        $this->getJson('/api/brands')
            ->assertOk()
            ->assertJsonPath('data.0.perfumes_count', 1);

        $this->getJson('/api/brands/test-brand')
            ->assertOk()
            ->assertJsonPath('data.perfumes.0.slug', 'published-count')
            ->assertJsonMissing(['slug' => 'draft-count']);

        AromaCategory::create([
            'name' => 'Fresh / Clean',
            'slug' => 'fresh-clean',
        ]);

        $this->getJson('/api/aroma-categories')->assertOk()->assertJsonPath('data.0.slug', $category->slug);
        $this->getJson('/api/aroma-categories')->assertOk()->assertJsonMissing(['slug' => 'fresh-clean']);
        $this->getJson('/api/aroma-tags')->assertOk()->assertJsonFragment(['slug' => $tag->slug]);
        $this->getJson('/api/occasions')->assertOk()->assertJsonFragment(['slug' => $occasion->slug]);
    }

    /**
     * @return array{Brand, AromaCategory, AromaTag, Occasion, Note}
     */
    private function createReferenceData(): array
    {
        $brand = Brand::create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
        ]);
        $category = AromaCategory::create([
            'name' => 'Fresh',
            'slug' => 'fresh',
            'description' => 'Aroma segar dan bersih.',
        ]);
        $tag = AromaTag::create([
            'name' => 'Citrus',
            'slug' => 'citrus',
            'description' => 'Nuansa jeruk.',
        ]);
        $occasion = Occasion::create([
            'name' => 'Kantor',
            'slug' => 'kantor',
            'description' => 'Cocok untuk suasana kerja.',
        ]);
        $note = Note::create([
            'name' => 'Bergamot',
            'slug' => 'bergamot',
            'description_simple' => 'Jeruk segar yang ringan.',
            'note_family' => 'citrus',
        ]);

        return [$brand, $category, $tag, $occasion, $note];
    }
}
