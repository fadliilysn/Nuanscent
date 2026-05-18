<?php

namespace Database\Seeders;

use App\Models\AromaCategory;
use Illuminate\Database\Seeder;

class AromaCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Fresh / Clean',
                'slug' => 'fresh-clean',
                'description' => 'Profil aroma yang terasa segar, ringan, bersih, atau seperti baru selesai dicuci.',
            ],
            [
                'name' => 'Sweet / Gourmand',
                'slug' => 'sweet-gourmand',
                'description' => 'Profil aroma manis yang mengingatkan pada dessert, krim, atau bahan makanan.',
            ],
            [
                'name' => 'Floral',
                'slug' => 'floral',
                'description' => 'Profil aroma yang berpusat pada kesan bunga, seperti mawar atau melati.',
            ],
            [
                'name' => 'Woody / Earthy',
                'slug' => 'woody-earthy',
                'description' => 'Profil aroma dengan kesan kayu, akar, lumut, kering, atau membumi.',
            ],
            [
                'name' => 'Warm / Amber / Spicy',
                'slug' => 'warm-amber-spicy',
                'description' => 'Profil aroma yang terasa hangat, resinous, berbumbu, atau lembut menyelimuti.',
            ],
            [
                'name' => 'Musky / Powdery / Soft',
                'slug' => 'musky-powdery-soft',
                'description' => 'Profil aroma yang terasa lembut, bersih, powdery, nyaman, atau dekat dengan aroma kulit.',
            ],
        ];

        foreach ($categories as $category) {
            AromaCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
