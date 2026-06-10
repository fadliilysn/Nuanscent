<?php

namespace Database\Seeders;

use App\Models\Perfume;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class NuanscentProductImageUrlPatchSeeder extends Seeder
{
    private const PATCH_PATH = 'seeders/data/nuanscent_product_image_url_patch.json';

    /**
     * @var array<string, mixed>
     */
    private array $payload;

    public function run(): void
    {
        $this->payload = $this->readPayload();
        $this->validatePerfumesExist();

        $patchedPerfumes = 0;

        DB::transaction(function () use (&$patchedPerfumes): void {
            foreach ($this->payload['patches'] as $patchData) {
                Perfume::query()
                    ->where('slug', $patchData['slug'])
                    ->firstOrFail()
                    ->forceFill([
                        'image_url' => $patchData['image_url'] ?? null,
                    ])
                    ->save();

                $patchedPerfumes++;
            }
        });

        $this->command?->info("Product image URL patch selesai: {$patchedPerfumes} parfum diperbarui.");
    }

    /**
     * @return array<string, mixed>
     */
    private function readPayload(): array
    {
        $path = database_path(self::PATCH_PATH);

        if (! file_exists($path)) {
            throw new RuntimeException("Patch image URL tidak ditemukan di {$path}.");
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload) || ! isset($payload['patches']) || ! is_array($payload['patches'])) {
            throw new RuntimeException('Format patch image URL tidak valid: key patches wajib tersedia.');
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
            throw new RuntimeException('Product image URL patch dihentikan. Perfume slug belum ada di database: '.implode(', ', $missingSlugs).'. Jalankan import data perfume terkait terlebih dahulu.');
        }
    }
}
