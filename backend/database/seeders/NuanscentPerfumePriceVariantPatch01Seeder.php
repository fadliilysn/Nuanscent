<?php

namespace Database\Seeders;

use App\Models\Perfume;
use App\Models\PerfumeVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class NuanscentPerfumePriceVariantPatch01Seeder extends Seeder
{
    private const PATCH_PATH = '/database/seeders/data/nuanscent_perfume_price_variant_patch_01.json';

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    public function run(): void
    {
        $this->payload = $this->readPayload();

        $pricePatched = 0;
        $variantsCreated = 0;
        $variantsUpdated = 0;
        $variantsSkipped = 0;
        $perfumesSkipped = 0;
        $missingSlugs = [];

        DB::transaction(function () use (
            &$pricePatched,
            &$variantsCreated,
            &$variantsUpdated,
            &$variantsSkipped,
            &$perfumesSkipped,
            &$missingSlugs,
        ): void {
            foreach ($this->payload['patches'] as $patchData) {
                $slug = (string) ($patchData['slug'] ?? '');

                if ($slug === '') {
                    $missingSlugs[] = '(slug kosong)';

                    continue;
                }

                $perfume = Perfume::query()
                    ->where('slug', $slug)
                    ->first();

                if (! $perfume) {
                    $missingSlugs[] = $slug;

                    continue;
                }

                $prices = [
                    'price_min' => $patchData['price_min'] ?? null,
                    'price_max' => $patchData['price_max'] ?? null,
                ];
                $pricesChanged = $perfume->price_min !== $prices['price_min']
                    || $perfume->price_max !== $prices['price_max'];
                $changed = false;

                foreach ($patchData['variants'] ?? [] as $variantData) {
                    $variant = $this->findVariant($perfume, $variantData);
                    $attributes = [
                        'label' => $variantData['name'] ?? null,
                        'volume_ml' => $variantData['size_ml'] ?? null,
                        'price' => $variantData['price'] ?? null,
                    ];

                    if (! $variant) {
                        $perfume->variants()->create($attributes);
                        $variantsCreated++;
                        $changed = true;

                        continue;
                    }

                    if ($this->variantMatches($variant, $attributes)) {
                        $variantsSkipped++;

                        continue;
                    }

                    $variant->fill($attributes)->save();
                    $variantsUpdated++;
                    $changed = true;
                }

                if (
                    $perfume->price_min !== $prices['price_min']
                    || $perfume->price_max !== $prices['price_max']
                ) {
                    $perfume->forceFill($prices)->saveQuietly();
                }

                if ($pricesChanged) {
                    $pricePatched++;
                    $changed = true;
                }

                if (! $changed) {
                    $perfumesSkipped++;
                }
            }
        });

        $manualReviews = collect($this->payload['missing_or_needs_manual_review'])
            ->filter(fn (array $item): bool => filled($item['slug'] ?? null))
            ->values();

        $this->command?->info('Perfume price & variant patch 01 selesai.');
        $this->command?->info("Harga parfum diperbarui: {$pricePatched}");
        $this->command?->info("Variant dibuat: {$variantsCreated}");
        $this->command?->info("Variant diperbarui: {$variantsUpdated}");
        $this->command?->info("Variant sudah sama: {$variantsSkipped}");
        $this->command?->info("Parfum sudah sama seluruhnya: {$perfumesSkipped}");

        if ($missingSlugs !== []) {
            $this->command?->warn('Perfume slug tidak ditemukan dan dilewati: '.implode(', ', $missingSlugs));
        } else {
            $this->command?->info('Perfume slug tidak ditemukan: tidak ada.');
        }

        if ($manualReviews->isEmpty()) {
            $this->command?->info('Masih perlu review manual: tidak ada.');

            return;
        }

        $this->command?->warn('Masih perlu review manual:');

        foreach ($manualReviews as $item) {
            $this->command?->line('- '.$item['slug'].': '.$item['reason']);
        }
    }

    /**
     * @param  array<string, mixed>  $variantData
     */
    private function findVariant(Perfume $perfume, array $variantData): ?PerfumeVariant
    {
        $size = $variantData['size_ml'] ?? null;
        $name = $variantData['name'] ?? null;

        if ($size !== null) {
            $variant = $perfume->variants()
                ->where('volume_ml', $size)
                ->first();

            if ($variant) {
                return $variant;
            }
        }

        if ($name !== null) {
            return $perfume->variants()
                ->where('label', $name)
                ->first();
        }

        return null;
    }

    /**
     * @param  array{label: mixed, volume_ml: mixed, price: mixed}  $attributes
     */
    private function variantMatches(PerfumeVariant $variant, array $attributes): bool
    {
        return $variant->label === $attributes['label']
            && $variant->volume_ml === $attributes['volume_ml']
            && $variant->price === $attributes['price'];
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(): array
    {
        $path = base_path(self::PATCH_PATH);

        if (! file_exists($path)) {
            throw new RuntimeException("Patch price & variant tidak ditemukan di {$path}.");
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (
            ! is_array($payload)
            || ! isset($payload['patches'], $payload['missing_or_needs_manual_review'])
            || ! is_array($payload['patches'])
            || ! is_array($payload['missing_or_needs_manual_review'])
        ) {
            throw new RuntimeException('Format patch price & variant tidak valid: key patches dan missing_or_needs_manual_review wajib tersedia.');
        }

        return $payload;
    }
}
