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

class NuanscentUnderrepresentedAromaBatch01Seeder extends Seeder
{
    private const DATASET_PATH = 'seeders/data/nuanscent_perfumes_underrepresented_aroma_batch_01.json';

    private const EXPECTED_SLUGS = [
        'mykonos-baby-love',
        'mykonos-pink-drops',
        'mykonos-dreamscape',
        'saff-co-loui',
        'saff-co-omnia-travel-size',
        'mykonos-vanilla-clouds',
        'mykonos-caramel-fudge-cookie',
        'mykonos-pink-beach',
        'saff-co-irai-leima',
    ];

    private const EXPECTED_CATEGORY_COUNTS = [
        'soft' => 3,
        'musky' => 2,
        'sweet' => 2,
        'powdery' => 2,
    ];

    private const DUPLICATE_PERFUME_SLUGS = [
        'saff-co-loui' => 'loui',
    ];

    private const CANONICAL_SLUG_ALIASES = [
        'saff-co-omnia-travel-size' => 'saff-co-omnia',
    ];

    private const OCCASION_ALIASES = [
        'santai' => 'casual-hangout',
        'keluarga' => 'daily',
        'hangout' => 'casual-hangout',
        'kencan' => 'date',
    ];

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    /**
     * @var array<string, int>
     */
    private array $counts = [
        'perfumes_created' => 0,
        'perfumes_skipped' => 0,
        'tags_created' => 0,
        'tags_synced' => 0,
        'occasions_synced' => 0,
        'notes_created' => 0,
        'notes_synced' => 0,
        'variants_created' => 0,
    ];

    /**
     * @var array<int, string>
     */
    private array $duplicatePreventedSlugs = [];

    public function run(): void
    {
        $this->payload = $this->readPayload();
        $this->validateDataset();
        $this->validateMasterReferences();

        DB::transaction(function (): void {
            $this->ensureAromaTags();

            $brands = Brand::query()->get()->keyBy('slug');
            $categories = AromaCategory::query()->get()->keyBy('slug');
            $tags = AromaTag::query()->get()->keyBy('slug');
            $occasions = Occasion::query()->get()->keyBy('slug');

            foreach ($this->payload['perfumes'] as $perfumeData) {
                $sourceSlug = (string) $perfumeData['slug'];

                if (isset(self::DUPLICATE_PERFUME_SLUGS[$sourceSlug])) {
                    $canonicalSlug = self::DUPLICATE_PERFUME_SLUGS[$sourceSlug];
                    $canonicalPerfume = Perfume::query()
                        ->where('slug', $canonicalSlug)
                        ->firstOrFail();

                    $canonicalPerfume->forceFill([
                        'main_aroma_category_id' => $categories[$perfumeData['main_aroma_category']]->id,
                    ])->save();

                    $this->duplicatePreventedSlugs[] = "{$sourceSlug} -> {$canonicalSlug}";
                    $this->counts['perfumes_skipped']++;

                    continue;
                }

                $slug = self::CANONICAL_SLUG_ALIASES[$sourceSlug] ?? $sourceSlug;

                if (Perfume::query()->where('slug', $slug)->exists()) {
                    $this->duplicatePreventedSlugs[] = $sourceSlug === $slug
                        ? $slug
                        : "{$sourceSlug} -> {$slug}";
                    $this->counts['perfumes_skipped']++;

                    continue;
                }

                $perfume = Perfume::query()->create([
                    'brand_id' => $brands[$this->brandSlugFor($perfumeData)]->id,
                    'name' => $perfumeData['name'],
                    'slug' => $slug,
                    'short_description' => $perfumeData['description'] ?? null,
                    'official_description' => null,
                    'concentration' => null,
                    'volume_ml' => null,
                    'price_min' => $perfumeData['price_min'] ?? null,
                    'price_max' => $perfumeData['price_max'] ?? null,
                    'image_url' => $perfumeData['image_url'] ?? null,
                    'marketed_gender' => null,
                    'intensity' => null,
                    'main_aroma_category_id' => $categories[$perfumeData['main_aroma_category']]->id,
                    'source_url' => $perfumeData['source_url'] ?? null,
                    'source_name' => $perfumeData['source_name'] ?? null,
                    'last_verified_at' => null,
                    'data_status' => 'published',
                ]);

                $this->counts['perfumes_created']++;
                $this->syncAromaTags($perfume, $perfumeData['aroma_tags'] ?? [], $tags);
                $this->syncOccasions($perfume, $perfumeData['occasions'] ?? [], $occasions);
                $this->syncNotes($perfume, $perfumeData['notes'] ?? []);
                $this->syncVariants($perfume, $perfumeData['variants'] ?? []);
            }
        });

        $this->reportSummary();
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(): array
    {
        $path = database_path(self::DATASET_PATH);

        if (! file_exists($path)) {
            throw new RuntimeException("Dataset underrepresented aroma tidak ditemukan di {$path}.");
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (
            ! is_array($payload)
            || ! isset($payload['perfumes'])
            || ! is_array($payload['perfumes'])
        ) {
            throw new RuntimeException('Format dataset underrepresented aroma tidak valid: key perfumes wajib tersedia.');
        }

        return $payload;
    }

    private function validateDataset(): void
    {
        $slugs = collect($this->payload['perfumes'])
            ->pluck('slug')
            ->filter()
            ->values();
        $duplicateSlugs = $slugs
            ->duplicates()
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($duplicateSlugs !== []) {
            throw new RuntimeException('Dataset memiliki slug parfum duplikat: '.implode(', ', $duplicateSlugs));
        }

        $missingSlugs = $this->missingSlugs(self::EXPECTED_SLUGS, $slugs->all());
        $unexpectedSlugs = $this->missingSlugs($slugs->all(), self::EXPECTED_SLUGS);

        if ($missingSlugs !== [] || $unexpectedSlugs !== []) {
            throw new RuntimeException(
                'Dataset harus berisi tepat 9 parfum kurasi. '
                .'Slug kurang: '.($missingSlugs === [] ? 'tidak ada' : implode(', ', $missingSlugs)).'. '
                .'Slug tidak diharapkan: '.($unexpectedSlugs === [] ? 'tidak ada' : implode(', ', $unexpectedSlugs)).'.',
            );
        }

        $categoryCounts = collect($this->payload['perfumes'])
            ->countBy('main_aroma_category')
            ->sortKeys()
            ->all();

        if ($categoryCounts !== collect(self::EXPECTED_CATEGORY_COUNTS)->sortKeys()->all()) {
            throw new RuntimeException('Jumlah kategori aroma dataset tidak sesuai dengan kurasi soft 3, musky 2, sweet 2, powdery 2.');
        }

        if ($slugs->contains('royal-ispahan') || $slugs->contains('mykonos-royal-ispahan')) {
            throw new RuntimeException('Royal Ispahan tidak boleh ditambahkan ulang dari dataset ini.');
        }
    }

    private function validateMasterReferences(): void
    {
        $missingBrands = $this->missingSlugs(
            collect($this->payload['perfumes'])
                ->map(fn (array $perfume): string => $this->brandSlugFor($perfume))
                ->unique()
                ->values()
                ->all(),
            Brand::query()->pluck('slug')->all(),
        );
        $missingCategories = $this->missingSlugs(
            collect($this->payload['perfumes'])
                ->pluck('main_aroma_category')
                ->unique()
                ->values()
                ->all(),
            AromaCategory::query()->pluck('slug')->all(),
        );
        $missingOccasions = $this->missingSlugs(
            collect($this->payload['perfumes'])
                ->flatMap(fn (array $perfume): array => $perfume['occasions'] ?? [])
                ->map(fn (string $occasion): string => $this->occasionSlugFor($occasion))
                ->unique()
                ->values()
                ->all(),
            Occasion::query()->pluck('slug')->all(),
        );

        $messages = [];

        if ($missingBrands !== []) {
            $messages[] = 'Brand slug belum tersedia di database: '.implode(', ', $missingBrands);
        }

        if ($missingCategories !== []) {
            $messages[] = 'Aroma category slug belum tersedia di database: '.implode(', ', $missingCategories);
        }

        if ($missingOccasions !== []) {
            $messages[] = 'Occasion slug hasil mapping belum tersedia di database: '.implode(', ', $missingOccasions);
        }

        if ($messages !== []) {
            throw new RuntimeException(implode(PHP_EOL, $messages));
        }
    }

    private function ensureAromaTags(): void
    {
        foreach ($this->aromaTagSlugs() as $slug) {
            $tag = AromaTag::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $slug,
                    'description' => null,
                    'is_polarizing' => false,
                ],
            );

            if ($tag->wasRecentlyCreated) {
                $this->counts['tags_created']++;
            }
        }
    }

    /**
     * @param  array<int, string>  $aromaTags
     * @param  \Illuminate\Support\Collection<string, AromaTag>  $tags
     */
    private function syncAromaTags(Perfume $perfume, array $aromaTags, $tags): void
    {
        $tagIds = collect($aromaTags)
            ->map(fn (string $slug): int => $tags[$slug]->id)
            ->unique()
            ->values()
            ->all();

        $perfume->aromaTags()->sync($tagIds);
        $this->counts['tags_synced'] += count($tagIds);
    }

    /**
     * @param  array<int, string>  $occasionLabels
     * @param  \Illuminate\Support\Collection<string, Occasion>  $occasions
     */
    private function syncOccasions(Perfume $perfume, array $occasionLabels, $occasions): void
    {
        $occasionIds = collect($occasionLabels)
            ->map(fn (string $occasion): string => $this->occasionSlugFor($occasion))
            ->map(fn (string $slug): int => $occasions[$slug]->id)
            ->unique()
            ->values()
            ->all();

        $perfume->occasions()->sync($occasionIds);
        $this->counts['occasions_synced'] += count($occasionIds);
    }

    /**
     * @param  array<string, array<int, string>>  $notesByPosition
     */
    private function syncNotes(Perfume $perfume, array $notesByPosition): void
    {
        foreach ($notesByPosition as $position => $noteNames) {
            $normalizedPosition = in_array($position, ['top', 'middle', 'base', 'unspecified'], true)
                ? $position
                : 'unspecified';

            foreach (array_unique($noteNames) as $name) {
                $note = Note::query()->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name],
                );

                if ($note->wasRecentlyCreated) {
                    $this->counts['notes_created']++;
                }

                $perfume->notes()->attach($note->id, [
                    'position' => $normalizedPosition,
                ]);
                $this->counts['notes_synced']++;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $variants
     */
    private function syncVariants(Perfume $perfume, array $variants): void
    {
        foreach ($variants as $variantData) {
            $perfume->variants()->create([
                'label' => $variantData['name'] ?? null,
                'volume_ml' => $variantData['size_ml'] ?? null,
                'price' => $variantData['price'] ?? null,
            ]);
            $this->counts['variants_created']++;
        }

        if (collect($variants)->contains(fn (array $variant): bool => isset($variant['price']))) {
            $perfume->refreshPriceRangeFromVariants(clearWhenNoVariants: true);
        }
    }

    private function reportSummary(): void
    {
        $this->command?->info('Underrepresented Aroma Batch 01 import selesai.');
        $this->command?->info("Parfum dibuat: {$this->counts['perfumes_created']}");
        $this->command?->info("Parfum dilewati karena slug sudah ada: {$this->counts['perfumes_skipped']}");
        $this->command?->info("Aroma tag master dibuat: {$this->counts['tags_created']}");
        $this->command?->info("Aroma tag disinkronkan: {$this->counts['tags_synced']}");
        $this->command?->info("Occasion disinkronkan: {$this->counts['occasions_synced']}");
        $this->command?->info("Note master dibuat: {$this->counts['notes_created']}");
        $this->command?->info("Note pivot disinkronkan: {$this->counts['notes_synced']}");
        $this->command?->info("Variant dibuat: {$this->counts['variants_created']}");

        if ($this->duplicatePreventedSlugs === []) {
            $this->command?->info('Duplicate-prevented slug: tidak ada.');
        } else {
            $this->command?->warn('Duplicate-prevented slug: '.implode(', ', $this->duplicatePreventedSlugs));
        }

        $this->reportReviewList('missing_or_needs_manual_review');
        $this->reportReviewList('manual_review_candidates');
    }

    private function reportReviewList(string $key): void
    {
        $items = $this->payload[$key] ?? [];

        if ($items === []) {
            $this->command?->info("{$key}: tidak ada.");

            return;
        }

        $this->command?->warn("{$key}:");

        foreach ($items as $item) {
            $this->command?->line('- '.(is_array($item) ? json_encode($item) : $item));
        }
    }

    /**
     * @param  array<string, mixed>  $perfumeData
     */
    private function brandSlugFor(array $perfumeData): string
    {
        return Str::slug((string) $perfumeData['brand']);
    }

    private function occasionSlugFor(string $occasion): string
    {
        return self::OCCASION_ALIASES[$occasion] ?? $occasion;
    }

    /**
     * @return array<int, string>
     */
    private function aromaTagSlugs(): array
    {
        return collect($this->payload['perfumes'])
            ->flatMap(fn (array $perfume): array => $perfume['aroma_tags'] ?? [])
            ->filter()
            ->unique()
            ->sort()
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
