<?php

namespace Database\Seeders;

use App\Models\AromaCategory;
use App\Support\AromaCategoryCatalog;
use Illuminate\Database\Seeder;

class AromaCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (AromaCategoryCatalog::CATEGORIES as $category) {
            AromaCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
