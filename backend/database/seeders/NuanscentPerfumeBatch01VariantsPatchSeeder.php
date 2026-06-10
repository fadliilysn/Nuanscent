<?php

namespace Database\Seeders;

use App\Models\Perfume;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class NuanscentPerfumeBatch01VariantsPatchSeeder extends Seeder
{
    private const PATCH_PATH = 'seeders/data/nuanscent_perfumes_batch_01_variants_patch.json';

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    public function run(): void
    {
        $this->payload = $this->readPayload();
        $this->validatePerfumesExist();

        $patchedPerfumes = 0;
        $syncedVariants = 0;

        DB::transaction(function () use (&$patchedPerfumes, &$syncedVariants): void {
            foreach ($this->payload['patches'] as $patchData) {
                $perfume = Perfume::query()
                    ->where('slug', $patchData['slug'])
                    ->firstOrFail();

                $variants = $patchData['variants'] ?? [];

                $this->syncVariants($perfume, $variants);
                $perfume->refreshPriceRangeFromVariants(clearWhenNoVariants: true);

                $patchedPerfumes++;
                $syncedVariants += count($variants);
            }
        });

        $this->command?->info("Batch 01 variants patch selesai: {$patchedPerfumes} parfum dipatch, {$syncedVariants} varian disinkronkan.");
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(): array
    {
        $path = database_path(self::PATCH_PATH);

        if (! file_exists($path)) {
            throw new RuntimeException("Patch variants tidak ditemukan di {$path}.");
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload) || ! isset($payload['patches']) || ! is_array($payload['patches'])) {
            throw new RuntimeException('Format patch variants tidak valid: key patches wajib tersedia.');
        }

        return $payload;
    }

    private function validatePerfumesExist(): void
    {
        $patchSlugs = collect($this->payload['patches'])
            ->pluck('slug')
            ->filter()
            ->unique()
            ->values();

        $existingSlugs = Perfume::query()
            ->whereIn('slug', $patchSlugs)
            ->pluck('slug');

        $missingSlugs = $patchSlugs
            ->diff($existingSlugs)
            ->sort()
            ->values()
            ->all();

        if ($missingSlugs !== []) {
            throw new RuntimeException('Batch 01 variants patch dihentikan. Perfume slug belum ada di database: '.implode(', ', $missingSlugs).'. Jalankan import Batch 01 terlebih dahulu.');
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $variants
     */
    private function syncVariants(Perfume $perfume, array $variants): void
    {
        $perfume->variants()->delete();

        foreach ($variants as $variantData) {
            $perfume->variants()->create([
                'label' => $variantData['label'] ?? null,
                'volume_ml' => $variantData['volume_ml'] ?? null,
                'price' => array_key_exists('price', $variantData) ? $variantData['price'] : null,
            ]);
        }
    }
}
