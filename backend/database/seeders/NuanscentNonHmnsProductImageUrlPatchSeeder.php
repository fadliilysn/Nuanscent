<?php

namespace Database\Seeders;

use App\Models\Perfume;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class NuanscentNonHmnsProductImageUrlPatchSeeder extends Seeder
{
    private const PATCH_PATH = '/database/seeders/data/nuanscent_non_hmns_product_image_url_patch.json';

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    public function run(): void
    {
        $this->payload = $this->readPayload();

        $patchedPerfumes = 0;
        $unchangedPerfumes = 0;
        $missingSlugs = [];

        DB::transaction(function () use (&$patchedPerfumes, &$unchangedPerfumes, &$missingSlugs): void {
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
                    $unchangedPerfumes++;

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

        $this->command?->info("Non-HMNS product image URL patch selesai: {$patchedPerfumes} parfum diperbarui, {$unchangedPerfumes} sudah sama.");

        if ($missingSlugs !== []) {
            $this->command?->warn('Perfume slug tidak ditemukan dan dilewati: '.implode(', ', $missingSlugs));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(): array
    {
        $path = base_path(self::PATCH_PATH);

        if (! file_exists($path)) {
            throw new RuntimeException("Patch image URL non-HMNS tidak ditemukan di {$path}.");
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload) || ! isset($payload['patches']) || ! is_array($payload['patches'])) {
            throw new RuntimeException('Format patch image URL non-HMNS tidak valid: key patches wajib tersedia.');
        }

        return $payload;
    }
}
