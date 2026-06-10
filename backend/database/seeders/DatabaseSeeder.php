<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AromaCategorySeeder::class,
            OccasionSeeder::class,
            AromaTagSeeder::class,
            NoteSeeder::class,
            NuanscentPerfumeBatch01Seeder::class,
            NuanscentPerfumeBatch01VariantsPatchSeeder::class,
            NuanscentPerfumeBatch02Seeder::class,
            NuanscentPerfumeBatch03Seeder::class,
            NuanscentPerfumeCleanBatch04Seeder::class,
            NuanscentUnderrepresentedAromaBatch01Seeder::class,
            RemapPerfumeAromaCategoriesSeeder::class,
            InitialGuideSeeder::class,
            NuanscentNoteEnrichmentSeeder::class,
            NuanscentProductImageUrlPatchSeeder::class,
            NuanscentNonHmnsProductImageUrlPatchSeeder::class,
            NuanscentProductImageUrlPatchBatch02Seeder::class,
            NuanscentPerfumePriceVariantPatch01Seeder::class,
            NuanscentFreshInstallCatalogStatePatchSeeder::class,
        ]);
    }
}
