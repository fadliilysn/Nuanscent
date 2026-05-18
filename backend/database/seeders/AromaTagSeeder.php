<?php

namespace Database\Seeders;

use App\Models\AromaTag;
use Illuminate\Database\Seeder;

class AromaTagSeeder extends Seeder
{
    public function run(): void
    {
        $polarizingTags = [
            'smoky',
            'leathery',
            'tobacco',
            'oud',
        ];

        $tags = [
            'citrus',
            'aquatic',
            'clean',
            'soapy',
            'fruity',
            'vanilla',
            'caramel',
            'coffee',
            'tea',
            'creamy',
            'rose',
            'jasmine',
            'white-floral',
            'cedar',
            'sandalwood',
            'vetiver',
            'patchouli',
            'amber',
            'spicy',
            'saffron',
            'musky',
            'powdery',
            'smoky',
            'leathery',
            'tobacco',
            'oud',
        ];

        foreach ($tags as $tag) {
            AromaTag::updateOrCreate(
                ['slug' => $tag],
                [
                    'name' => $tag,
                    'slug' => $tag,
                    'description' => null,
                    'is_polarizing' => in_array($tag, $polarizingTags, true),
                ],
            );
        }
    }
}
