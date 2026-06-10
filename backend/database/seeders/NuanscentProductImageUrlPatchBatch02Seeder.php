<?php

namespace Database\Seeders;

use App\Models\Perfume;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class NuanscentProductImageUrlPatchBatch02Seeder extends Seeder
{
    private const PATCH_PATH = 'seeders/data/nuanscent_product_image_url_patch_batch_02.json';

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    public function run(): void
    {
        $this->payload = $this->readPayload();

        $patchedPerfumes = 0;
        $skippedPerfumes = 0;
        $missingSlugs = [];

        DB::transaction(function () use (&$patchedPerfumes, &$skippedPerfumes, &$missingSlugs): void {
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

                $imageUrl = $patchData['image_url'] ?? null;

                if ($perfume->image_url === $imageUrl) {
                    $skippedPerfumes++;

                    continue;
                }

                $perfume
                    ->forceFill([
                        'image_url' => $imageUrl,
                    ])
                    ->save();

                $patchedPerfumes++;
            }
        });

        $manualReviewSlugs = collect($this->payload['missing_or_needs_manual_review'])
            ->filter()
            ->values()
            ->all();

        $this->command?->info("Product image URL patch Batch 02 selesai: {$patchedPerfumes} parfum diperbarui, {$skippedPerfumes} sudah sama.");

        if ($missingSlugs !== []) {
            $this->command?->warn('Perfume slug tidak ditemukan dan dilewati: '.implode(', ', $missingSlugs));
        } else {
            $this->command?->info('Perfume slug tidak ditemukan: tidak ada.');
        }

        if ($manualReviewSlugs !== []) {
            $this->command?->warn('Masih perlu review manual: '.implode(', ', $manualReviewSlugs));
        } else {
            $this->command?->info('Masih perlu review manual: tidak ada.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(): array
    {
        $path = database_path(self::PATCH_PATH);

        if (! file_exists($path)) {
            throw new RuntimeException("Patch image URL Batch 02 tidak ditemukan di {$path}.");
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (
            ! is_array($payload)
            || ! isset($payload['patches'], $payload['missing_or_needs_manual_review'])
            || ! is_array($payload['patches'])
            || ! is_array($payload['missing_or_needs_manual_review'])
        ) {
            throw new RuntimeException('Format patch image URL Batch 02 tidak valid: key patches dan missing_or_needs_manual_review wajib tersedia.');
        }

        return $payload;
    }
}
