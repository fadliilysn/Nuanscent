<?php

namespace Database\Seeders;

use App\Models\Guide;
use Illuminate\Database\Seeder;

class InitialGuideSeeder extends Seeder
{
    /**
     * Seed beginner-friendly public guide content.
     */
    public function run(): void
    {
        $guides = [
            [
                'title' => 'Cara Memilih Parfum Lokal untuk Pemula',
                'slug' => 'cara-memilih-parfum-lokal-untuk-pemula',
                'excerpt' => 'Langkah sederhana untuk mulai memilih parfum lokal berdasarkan kebutuhan, aroma, budget, dan risiko blind buy.',
                'body' => <<<'TEXT'
Memilih parfum pertama sering terasa membingungkan karena pilihan aromanya banyak sekali. Cara paling mudah adalah mulai dari kebutuhanmu, bukan dari istilah yang rumit.

Tentukan dulu parfum itu akan dipakai untuk apa. Untuk harian, kampus, atau kantor, biasanya aroma yang bersih, segar, atau lembut lebih mudah diterima. Untuk acara malam, date, atau suasana yang lebih formal, kamu bisa mempertimbangkan aroma yang lebih hangat, manis, woody, atau amber.

Setelah tahu occasion, pilih keluarga aroma yang paling dekat dengan seleramu. Kalau suka kesan mandi bersih, cari fresh atau clean. Kalau suka aroma nyaman seperti dessert, pilih sweet atau gourmand. Kalau suka kesan rapi dan dewasa, woody atau musky bisa jadi titik awal.

Tentukan juga budget sebelum terlalu jauh membandingkan parfum. Di Nuanscent, kamu bisa memakai filter harga agar pilihan tidak melebar ke produk yang belum sesuai. Ini membantu kamu fokus pada parfum yang realistis untuk dibeli.

Kalau ingin blind buy, jangan langsung memilih hanya karena namanya menarik. Baca ringkasan, aroma utama, tags, dan catatan kehati-hatian. Jika kamu masih ragu, pilih ukuran kecil atau sample ketika tersedia.

Bandingkan dua sampai tiga kandidat saja. Terlalu banyak pilihan bisa membuat keputusan makin sulit. Pilih yang paling sesuai dengan occasion, aroma favorit, budget, dan tingkat kenyamananmu terhadap aroma yang belum pernah dicoba.
TEXT,
                'published_at' => '2026-05-17 09:00:00',
            ],
            [
                'title' => 'Mengenal Top, Middle, dan Base Notes',
                'slug' => 'mengenal-top-middle-dan-base-notes',
                'excerpt' => 'Penjelasan sederhana tentang perubahan aroma parfum dari semprotan pertama sampai aroma yang bertahan lebih lama.',
                'body' => <<<'TEXT'
Parfum bisa berubah aromanya setelah dipakai. Itu sebabnya kesan semprotan pertama kadang berbeda dengan aroma yang tertinggal setelah beberapa jam.

Top notes adalah aroma yang paling cepat tercium saat parfum baru disemprotkan. Bagian ini biasanya terasa ringan, segar, atau menarik perhatian. Contohnya bisa berupa citrus, fruity, herbal, atau nuansa clean.

Middle notes sering disebut sebagai karakter utama parfum. Aromanya muncul setelah top notes mulai mereda. Di bagian ini kamu biasanya mulai merasakan arah parfum yang sebenarnya, misalnya floral, spicy, sweet, atau aromatic.

Base notes adalah aroma yang paling lama bertahan. Bagian ini memberi kesan akhir pada parfum, seperti woody, musk, amber, vanilla, atau sandalwood. Base notes sering terasa lebih hangat dan menempel lebih lama di kulit atau pakaian.

Perubahan ini penting saat memilih parfum. Jangan menilai parfum hanya dari semprotan pertama, terutama jika kamu mencoba langsung di toko. Beri waktu beberapa menit agar middle notes muncul, lalu lihat apakah aromanya masih nyaman.

Saat membaca detail parfum di Nuanscent, gunakan notes sebagai petunjuk arah, bukan janji mutlak. Aroma bisa terasa berbeda di setiap orang karena kulit, cuaca, jumlah semprotan, dan preferensi pribadi ikut berpengaruh.
TEXT,
                'published_at' => '2026-05-18 09:00:00',
            ],
            [
                'title' => 'Apa Itu Fresh, Sweet, Floral, Woody, dan Amber?',
                'slug' => 'apa-itu-fresh-sweet-floral-woody-dan-amber',
                'excerpt' => 'Kenali keluarga aroma umum agar lebih mudah membaca katalog dan hasil rekomendasi parfum.',
                'body' => <<<'TEXT'
Keluarga aroma membantu kamu memahami arah sebuah parfum tanpa harus hafal semua notes. Ini seperti peta sederhana untuk membaca karakter parfum.

Fresh biasanya memberi kesan segar, bersih, ringan, atau seperti baru selesai mandi. Aroma ini sering cocok untuk siang hari, cuaca panas, kampus, kantor, atau pemakaian harian.

Sweet atau gourmand memberi kesan manis, creamy, dessert-like, atau nyaman. Keluarga aroma ini bisa terasa hangat dan menyenangkan, tetapi sebagian orang mungkin merasa terlalu manis jika dipakai terlalu banyak.

Floral berhubungan dengan bunga seperti rose, jasmine, white floral, atau bouquet. Kesan floral bisa lembut, elegan, bersih, romantis, atau cukup kuat tergantung komposisinya.

Woody memberi kesan kayu, kering, rapi, dewasa, atau tenang. Aroma seperti sandalwood, cedar, vetiver, dan patchouli sering masuk wilayah ini. Woody bisa cocok untuk kantor, acara formal, atau suasana yang lebih kalem.

Amber biasanya terasa hangat, resinous, spicy, atau sedikit manis. Keluarga aroma ini sering terasa lebih bold dan cocok untuk malam hari, date, atau acara yang membutuhkan kesan lebih menonjol.

Tidak ada keluarga aroma yang paling benar untuk semua orang. Gunakan kategori ini untuk mempersempit pilihan, lalu cocokkan lagi dengan occasion, budget, intensitas, dan kenyamanan blind buy.
TEXT,
                'published_at' => '2026-05-19 09:00:00',
            ],
            [
                'title' => 'Tips Blind Buy Parfum agar Tidak Salah Pilih',
                'slug' => 'tips-blind-buy-parfum-agar-tidak-salah-pilih',
                'excerpt' => 'Cara mengurangi risiko saat membeli parfum tanpa mencoba langsung terlebih dahulu.',
                'body' => <<<'TEXT'
Blind buy berarti membeli parfum tanpa mencobanya langsung. Ini bisa menyenangkan, tetapi risikonya juga nyata karena aroma sangat personal.

Langkah pertama adalah membaca notes, kategori aroma, dan aroma tags. Cari pola yang sudah kamu tahu nyaman untukmu. Jika biasanya kamu suka fresh dan clean, jangan langsung lompat ke aroma smoky, oud, atau sweet yang sangat tebal tanpa pertimbangan.

Perhatikan notes yang cenderung polarizing. Beberapa aroma seperti oud, leather, tobacco, smoky, atau sweetness yang sangat kuat bisa terasa menarik bagi sebagian orang, tetapi terlalu berat bagi yang lain.

Mulailah dari ukuran kecil jika tersedia. Decant, sample, travel size, atau botol kecil bisa membantu kamu mencoba aroma tanpa langsung membeli ukuran besar.

Cocokkan pilihan dengan comfort level kamu. Kalau kamu mudah pusing dengan aroma kuat, pilih parfum dengan intensitas lebih lembut atau kategori yang aman untuk harian. Kalau kamu suka eksplorasi, kamu bisa mencoba aroma yang lebih unik, tetapi tetap pahami risikonya.

Gunakan label kehati-hatian blind buy di Nuanscent sebagai panduan, bukan keputusan akhir. Label itu membantu membaca risiko dari data yang tersedia, tetapi hidung dan kenyamananmu tetap menjadi penentu utama.
TEXT,
                'published_at' => '2026-05-20 09:00:00',
            ],
            [
                'title' => 'Cara Membaca Konsentrasi Parfum: EDT, EDP, dan Extrait',
                'slug' => 'cara-membaca-konsentrasi-parfum-edt-edp-dan-extrait',
                'excerpt' => 'Memahami istilah konsentrasi parfum dengan cara praktis, tanpa menganggap yang paling kuat selalu paling cocok.',
                'body' => <<<'TEXT'
Konsentrasi parfum sering muncul dalam bentuk singkatan seperti EDT, EDP, dan Extrait. Istilah ini memberi gambaran umum tentang intensitas dan karakter pemakaian, tetapi bukan satu-satunya penentu kualitas.

EDT atau Eau de Toilette biasanya terasa lebih ringan dan mudah dipakai. Banyak orang memilih EDT untuk aktivitas harian, cuaca panas, atau situasi yang membutuhkan aroma tidak terlalu tebal.

EDP atau Eau de Parfum biasanya terasa lebih penuh dibanding EDT. EDP sering dipilih ketika kamu ingin aroma yang lebih terasa, tetapi tetap bisa dipakai untuk banyak situasi jika komposisinya nyaman.

Extrait atau parfum extrait biasanya diasosiasikan dengan konsentrasi yang lebih tinggi. Namun, lebih kuat tidak selalu berarti lebih cocok. Aroma yang terlalu pekat bisa terasa berat jika dipakai di ruangan kecil, cuaca panas, atau aktivitas yang dekat dengan banyak orang.

Konsentrasi juga tidak otomatis menjamin parfum akan lebih enak atau lebih tahan lama di semua orang. Komposisi notes, cara semprot, cuaca, kulit, dan bahan yang digunakan juga berpengaruh.

Saat memilih di Nuanscent, gunakan konsentrasi sebagai salah satu petunjuk. Pertimbangkan juga occasion, intensitas, aroma utama, dan kenyamananmu. Parfum terbaik bukan selalu yang paling kuat, tetapi yang paling cocok dengan kebutuhanmu.
TEXT,
                'published_at' => '2026-05-21 09:00:00',
            ],
            [
                'title' => 'Cara Menyesuaikan Parfum dengan Occasion',
                'slug' => 'cara-menyesuaikan-parfum-dengan-occasion',
                'excerpt' => 'Panduan memilih karakter parfum untuk harian, kampus, kantor, date, acara formal, dan malam hari.',
                'body' => <<<'TEXT'
Occasion membantu kamu memilih parfum yang terasa pas dengan situasi. Aroma yang cocok untuk malam hari belum tentu nyaman untuk kelas, kantor, atau perjalanan harian.

Untuk daily use, pilih aroma yang mudah diterima dan tidak terlalu mengganggu. Fresh, clean, musky lembut, citrus, tea, atau floral ringan sering menjadi pilihan aman.

Untuk kampus, cari parfum yang terasa rapi, segar, dan tidak terlalu menusuk. Kamu akan berada dekat dengan orang lain, jadi intensitas yang soft sampai medium biasanya lebih nyaman.

Untuk kantor, pilih aroma yang memberi kesan bersih dan profesional. Fresh, woody ringan, musky, powdery, atau floral halus bisa bekerja baik selama tidak disemprot berlebihan.

Untuk date, kamu bisa memilih aroma yang lebih personal dan memorable. Sweet lembut, warm, amber, floral, musky, atau woody creamy bisa terasa menarik, asalkan tetap nyaman untuk kamu dan orang di sekitarmu.

Untuk acara formal, aroma woody, amber, musky, floral elegan, atau spicy yang tertata bisa memberi kesan lebih matang. Pakai secukupnya agar aromanya hadir tanpa mendominasi ruangan.

Untuk malam hari, kamu punya ruang lebih luas untuk aroma yang hangat, manis, bold, atau sensual. Tetap perhatikan tempat dan cuaca, karena parfum yang terlalu berat bisa terasa berlebihan di situasi tertentu.
TEXT,
                'published_at' => '2026-05-22 09:00:00',
            ],
        ];

        foreach ($guides as $guide) {
            Guide::query()->updateOrCreate(
                ['slug' => $guide['slug']],
                [
                    'title' => $guide['title'],
                    'excerpt' => $guide['excerpt'],
                    'body' => $guide['body'],
                    'status' => 'published',
                    'published_at' => $guide['published_at'],
                ],
            );
        }
    }
}
