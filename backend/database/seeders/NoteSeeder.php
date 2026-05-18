<?php

namespace Database\Seeders;

use App\Models\Note;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    public function run(): void
    {
        $notes = [
            [
                'name' => 'bergamot',
                'slug' => 'bergamot',
                'description_simple' => 'Note citrus yang cerah, sering memberi kesan segar dengan sedikit pahit yang ringan.',
                'note_family' => 'citrus',
            ],
            [
                'name' => 'lemon',
                'slug' => 'lemon',
                'description_simple' => 'Note citrus yang tajam, biasanya terasa segar, asam cerah, dan bersih.',
                'note_family' => 'citrus',
            ],
            [
                'name' => 'mandarin',
                'slug' => 'mandarin',
                'description_simple' => 'Note citrus yang lebih lembut, bisa terasa juicy, manis, dan cerah.',
                'note_family' => 'citrus',
            ],
            [
                'name' => 'aquatic',
                'slug' => 'aquatic',
                'description_simple' => 'Kesan aroma berair atau ringan yang sering muncul pada parfum bernuansa fresh.',
                'note_family' => 'fresh',
            ],
            [
                'name' => 'vanilla',
                'slug' => 'vanilla',
                'description_simple' => 'Note manis dan hangat yang bisa terasa creamy, lembut, atau seperti dessert.',
                'note_family' => 'gourmand',
            ],
            [
                'name' => 'caramel',
                'slug' => 'caramel',
                'description_simple' => 'Note manis yang bisa terasa seperti gula, hangat, dan mengarah ke dessert.',
                'note_family' => 'gourmand',
            ],
            [
                'name' => 'coffee',
                'slug' => 'coffee',
                'description_simple' => 'Note roasted yang bisa terasa gelap, pahit, hangat, atau gourmand.',
                'note_family' => 'gourmand',
            ],
            [
                'name' => 'tea',
                'slug' => 'tea',
                'description_simple' => 'Note aromatik yang lembut, bisa terasa menenangkan, leafy, atau sedikit pahit.',
                'note_family' => 'aromatic',
            ],
            [
                'name' => 'rose',
                'slug' => 'rose',
                'description_simple' => 'Note floral yang bisa terasa segar, lembut, jammy, atau romantis tergantung komposisinya.',
                'note_family' => 'floral',
            ],
            [
                'name' => 'jasmine',
                'slug' => 'jasmine',
                'description_simple' => 'Note white floral yang bisa terasa kaya, manis, dan elegan.',
                'note_family' => 'floral',
            ],
            [
                'name' => 'cedar',
                'slug' => 'cedar',
                'description_simple' => 'Note woody yang kering, bisa terasa bersih, seperti pensil kayu, atau memberi struktur.',
                'note_family' => 'woody',
            ],
            [
                'name' => 'sandalwood',
                'slug' => 'sandalwood',
                'description_simple' => 'Note woody yang halus, biasanya terasa creamy, lembut, dan hangat.',
                'note_family' => 'woody',
            ],
            [
                'name' => 'vetiver',
                'slug' => 'vetiver',
                'description_simple' => 'Note woody berkesan rumput atau akar, bisa terasa earthy, kering, smoky, atau rapi.',
                'note_family' => 'woody',
            ],
            [
                'name' => 'patchouli',
                'slug' => 'patchouli',
                'description_simple' => 'Note earthy yang bisa terasa woody, gelap, lembap, atau manis tergantung pemakaiannya.',
                'note_family' => 'earthy',
            ],
            [
                'name' => 'amber',
                'slug' => 'amber',
                'description_simple' => 'Kesan hangat yang bisa terasa resinous, manis, nyaman, atau seperti cahaya keemasan.',
                'note_family' => 'amber',
            ],
            [
                'name' => 'saffron',
                'slug' => 'saffron',
                'description_simple' => 'Note spicy yang bisa terasa hangat, sedikit leathery, dan agak manis.',
                'note_family' => 'spicy',
            ],
            [
                'name' => 'musk',
                'slug' => 'musk',
                'description_simple' => 'Note lembut yang bisa terasa bersih, seperti aroma kulit, powdery, atau nyaman.',
                'note_family' => 'musky',
            ],
            [
                'name' => 'leather',
                'slug' => 'leather',
                'description_simple' => 'Note yang tegas, bisa terasa smoky, kering, animalic, atau polished.',
                'note_family' => 'leathery',
            ],
            [
                'name' => 'tobacco',
                'slug' => 'tobacco',
                'description_simple' => 'Note hangat yang bisa terasa manis, kering, seperti daun, atau smoky.',
                'note_family' => 'tobacco',
            ],
            [
                'name' => 'oud',
                'slug' => 'oud',
                'description_simple' => 'Note woody yang dalam, bisa terasa smoky, resinous, gelap, atau intens.',
                'note_family' => 'woody',
            ],
        ];

        foreach ($notes as $note) {
            Note::updateOrCreate(
                ['slug' => $note['slug']],
                $note,
            );
        }
    }
}
