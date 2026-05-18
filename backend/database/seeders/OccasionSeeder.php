<?php

namespace Database\Seeders;

use App\Models\Occasion;
use Illuminate\Database\Seeder;

class OccasionSeeder extends Seeder
{
    public function run(): void
    {
        $occasions = [
            [
                'name' => 'Daily',
                'slug' => 'daily',
                'description' => 'Untuk pemakaian sehari-hari yang fleksibel.',
            ],
            [
                'name' => 'Campus',
                'slug' => 'campus',
                'description' => 'Untuk suasana sekolah atau kampus, biasanya cocok dengan aroma yang mudah diterima.',
            ],
            [
                'name' => 'Office',
                'slug' => 'office',
                'description' => 'Untuk suasana kerja, biasanya cocok dengan aroma yang rapi, seimbang, dan tidak terlalu mengganggu sekitar.',
            ],
            [
                'name' => 'Casual / Hangout',
                'slug' => 'casual-hangout',
                'description' => 'Untuk suasana santai, jalan-jalan, atau bertemu teman secara informal.',
            ],
            [
                'name' => 'Date',
                'slug' => 'date',
                'description' => 'Untuk suasana dekat atau personal, saat aroma yang berkesan bisa menjadi nilai tambah.',
            ],
            [
                'name' => 'Formal',
                'slug' => 'formal',
                'description' => 'Untuk acara yang lebih rapi, resmi, atau membutuhkan kesan lebih polished.',
            ],
            [
                'name' => 'Evening / Night',
                'slug' => 'evening-night',
                'description' => 'Untuk sore atau malam hari, saat aroma yang lebih kaya sering terasa lebih sesuai.',
            ],
        ];

        foreach ($occasions as $occasion) {
            Occasion::updateOrCreate(
                ['slug' => $occasion['slug']],
                $occasion,
            );
        }
    }
}
