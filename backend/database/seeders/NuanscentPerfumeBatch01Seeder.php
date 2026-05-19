<?php

namespace Database\Seeders;

use App\Models\AromaCategory;
use App\Models\AromaTag;
use App\Models\Brand;
use App\Models\Note;
use App\Models\Occasion;
use App\Models\Perfume;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class NuanscentPerfumeBatch01Seeder extends Seeder
{
    private const DATASET_PATH = '/../data/nuanscent_perfumes_batch_01.json';

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    public function run(): void
    {
        $this->payload = $this->readPayload();
        $this->validateMasterReferences();
        $this->validateBrandReferences();

        DB::transaction(function (): void {
            $brands = $this->importBrands();
            $categories = AromaCategory::query()->get()->keyBy('slug');
            $tags = AromaTag::query()->get()->keyBy('slug');
            $occasions = Occasion::query()->get()->keyBy('slug');

            foreach ($this->payload['perfumes'] as $perfumeData) {
                $perfume = Perfume::updateOrCreate(
                    ['slug' => $perfumeData['slug']],
                    [
                        'brand_id' => $brands[$perfumeData['brand_slug']]->id,
                        'name' => $perfumeData['name'],
                        'short_description' => $perfumeData['short_description'] ?? null,
                        'official_description' => $perfumeData['official_description'] ?? null,
                        'concentration' => $perfumeData['concentration'] ?? null,
                        'volume_ml' => $perfumeData['volume_ml'] ?? null,
                        'price_min' => $perfumeData['price_min'] ?? null,
                        'price_max' => $perfumeData['price_max'] ?? null,
                        'image_url' => $perfumeData['image_url'] ?? null,
                        'marketed_gender' => $perfumeData['marketed_gender'] ?? null,
                        'intensity' => $perfumeData['intensity'] ?? null,
                        'main_aroma_category_id' => $categories[$perfumeData['main_aroma_category_slug']]->id,
                        'source_url' => $perfumeData['source_url'] ?? null,
                        'source_name' => $perfumeData['source_name'] ?? null,
                        'last_verified_at' => $perfumeData['last_verified_at'] ?? null,
                        'data_status' => $perfumeData['data_status'],
                    ],
                );

                $perfume->aromaTags()->sync(
                    collect($perfumeData['aroma_tag_slugs'] ?? [])
                        ->map(fn (string $slug): int => $tags[$slug]->id)
                        ->all(),
                );

                $perfume->occasions()->sync(
                    collect($perfumeData['occasion_slugs'] ?? [])
                        ->map(fn (string $slug): int => $occasions[$slug]->id)
                        ->all(),
                );

                $this->syncNotes($perfume, $perfumeData['notes'] ?? []);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(): array
    {
        $path = base_path(self::DATASET_PATH);

        if (! file_exists($path)) {
            throw new RuntimeException("Dataset tidak ditemukan di {$path}.");
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload) || ! isset($payload['brands'], $payload['perfumes'])) {
            throw new RuntimeException('Format dataset tidak valid: key brands dan perfumes wajib tersedia.');
        }

        return $payload;
    }

    private function validateMasterReferences(): void
    {
        $missingCategories = $this->missingSlugs(
            $this->collectPerfumeValues('main_aroma_category_slug'),
            AromaCategory::query()->pluck('slug')->all(),
        );
        $missingTags = $this->missingSlugs(
            $this->collectPerfumeListValues('aroma_tag_slugs'),
            AromaTag::query()->pluck('slug')->all(),
        );
        $missingOccasions = $this->missingSlugs(
            $this->collectPerfumeListValues('occasion_slugs'),
            Occasion::query()->pluck('slug')->all(),
        );

        $messages = [];

        if ($missingCategories !== []) {
            $messages[] = 'Aroma category slug belum tersedia: '.implode(', ', $missingCategories);
        }

        if ($missingTags !== []) {
            $messages[] = 'Aroma tag slug belum tersedia: '.implode(', ', $missingTags);
        }

        if ($missingOccasions !== []) {
            $messages[] = 'Occasion slug belum tersedia: '.implode(', ', $missingOccasions);
        }

        if ($messages !== []) {
            throw new RuntimeException(implode(PHP_EOL, $messages));
        }
    }

    private function validateBrandReferences(): void
    {
        $brandSlugs = collect($this->payload['brands'])
            ->pluck('slug')
            ->merge(Brand::query()->pluck('slug'))
            ->unique()
            ->values()
            ->all();

        $missingBrands = $this->missingSlugs($this->collectPerfumeValues('brand_slug'), $brandSlugs);

        if ($missingBrands !== []) {
            throw new RuntimeException('Brand slug belum tersedia di dataset atau database: '.implode(', ', $missingBrands));
        }
    }

    /**
     * @return array<string, Brand>
     */
    private function importBrands(): array
    {
        $brands = [];

        foreach ($this->payload['brands'] as $brandData) {
            $brand = Brand::updateOrCreate(
                ['slug' => $brandData['slug']],
                [
                    'name' => $brandData['name'],
                    'description' => $brandData['description'] ?? null,
                    'official_website' => $brandData['official_website'] ?? null,
                    'logo_url' => $brandData['logo_url'] ?? null,
                ],
            );

            $brands[$brand->slug] = $brand;
        }

        foreach (Brand::query()->whereIn('slug', $this->collectPerfumeValues('brand_slug'))->get() as $brand) {
            $brands[$brand->slug] = $brand;
        }

        return $brands;
    }

    /**
     * @param  array<int, array<string, string>>  $notes
     */
    private function syncNotes(Perfume $perfume, array $notes): void
    {
        $perfume->notes()->detach();

        foreach ($notes as $noteData) {
            $note = Note::updateOrCreate(
                ['slug' => Str::slug($noteData['name'])],
                [
                    'name' => $noteData['name'],
                    'description_simple' => $noteData['description_simple'] ?? null,
                    'note_family' => $noteData['note_family'] ?? null,
                ],
            );

            $perfume->notes()->attach($note->id, [
                'position' => $noteData['position'] ?? 'unspecified',
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function collectPerfumeValues(string $key): array
    {
        return collect($this->payload['perfumes'])
            ->pluck($key)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function collectPerfumeListValues(string $key): array
    {
        return collect($this->payload['perfumes'])
            ->flatMap(fn (array $perfume): array => $perfume[$key] ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $expected
     * @param  array<int, string>  $available
     * @return array<int, string>
     */
    private function missingSlugs(array $expected, array $available): array
    {
        return collect($expected)
            ->diff($available)
            ->sort()
            ->values()
            ->all();
    }
}
