<?php

namespace Database\Seeders;

use App\Models\AromaCategory;
use App\Models\Perfume;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class NuanscentFreshInstallCatalogStatePatchSeeder extends Seeder
{
    private const PATCH_PATH = 'seeders/data/nuanscent_fresh_install_catalog_state_patch.json';

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    public function run(): void
    {
        $this->payload = $this->readPayload();
        $this->validateReferences();

        $patched = 0;
        $variantsSynced = 0;

        DB::transaction(function () use (&$patched, &$variantsSynced): void {
            $categories = AromaCategory::query()->get()->keyBy('slug');

            foreach ($this->payload['patches'] as $patchData) {
                $perfume = Perfume::query()
                    ->where('slug', $patchData['slug'])
                    ->firstOrFail();
                $updates = [];

                foreach (['image_url', 'price_min', 'price_max'] as $field) {
                    if (array_key_exists($field, $patchData)) {
                        $updates[$field] = $patchData[$field];
                    }
                }

                if (array_key_exists('main_aroma_category_slug', $patchData)) {
                    $updates['main_aroma_category_id'] = $categories[$patchData['main_aroma_category_slug']]->id;
                }

                if ($updates !== []) {
                    $perfume->forceFill($updates)->save();
                }

                if (array_key_exists('variants', $patchData)) {
                    $perfume->variants()->delete();

                    foreach ($patchData['variants'] as $variantData) {
                        $perfume->variants()->create([
                            'label' => $variantData['label'] ?? null,
                            'volume_ml' => $variantData['volume_ml'] ?? null,
                            'price' => $variantData['price'] ?? null,
                        ]);
                        $variantsSynced++;
                    }
                }

                $patched++;
            }
        });

        $this->command?->info(
            "Fresh install catalog state patch selesai: {$patched} parfum diperbarui, {$variantsSynced} varian disinkronkan.",
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(): array
    {
        $path = database_path(self::PATCH_PATH);

        if (! file_exists($path)) {
            throw new RuntimeException("Fresh install catalog state patch tidak ditemukan di {$path}.");
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload) || ! isset($payload['patches']) || ! is_array($payload['patches'])) {
            throw new RuntimeException('Format fresh install catalog state patch tidak valid: key patches wajib tersedia.');
        }

        return $payload;
    }

    private function validateReferences(): void
    {
        $patchSlugs = collect($this->payload['patches'])
            ->pluck('slug')
            ->filter()
            ->unique()
            ->values();
        $missingPerfumes = $patchSlugs
            ->diff(Perfume::query()->whereIn('slug', $patchSlugs)->pluck('slug'))
            ->sort()
            ->values()
            ->all();
        $categorySlugs = collect($this->payload['patches'])
            ->pluck('main_aroma_category_slug')
            ->filter()
            ->unique()
            ->values();
        $missingCategories = $categorySlugs
            ->diff(AromaCategory::query()->whereIn('slug', $categorySlugs)->pluck('slug'))
            ->sort()
            ->values()
            ->all();
        $messages = [];

        if ($missingPerfumes !== []) {
            $messages[] = 'Perfume slug belum tersedia: '.implode(', ', $missingPerfumes);
        }

        if ($missingCategories !== []) {
            $messages[] = 'Aroma category slug belum tersedia: '.implode(', ', $missingCategories);
        }

        if ($messages !== []) {
            throw new RuntimeException(implode(PHP_EOL, $messages));
        }
    }
}
