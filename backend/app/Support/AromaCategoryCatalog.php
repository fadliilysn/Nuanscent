<?php

namespace App\Support;

final class AromaCategoryCatalog
{
    public const CATEGORIES = [
        [
            'name' => 'Fresh',
            'slug' => 'fresh',
            'description' => 'Aroma segar, ringan, sering terasa citrus, aquatic, atau mudah dipakai harian.',
        ],
        [
            'name' => 'Clean',
            'slug' => 'clean',
            'description' => 'Aroma bersih, rapi, seperti sabun, laundry, atau baru selesai mandi.',
        ],
        [
            'name' => 'Sweet',
            'slug' => 'sweet',
            'description' => 'Aroma manis umum yang terasa nyaman tanpa harus selalu seperti dessert.',
        ],
        [
            'name' => 'Gourmand',
            'slug' => 'gourmand',
            'description' => 'Aroma dessert-like seperti vanilla, caramel, kopi, krim, atau nuansa makanan manis.',
        ],
        [
            'name' => 'Floral',
            'slug' => 'floral',
            'description' => 'Aroma bunga seperti mawar, melati, white floral, atau buket bunga.',
        ],
        [
            'name' => 'Woody',
            'slug' => 'woody',
            'description' => 'Aroma kayu seperti cedar, sandalwood, atau kesan kering dan rapi.',
        ],
        [
            'name' => 'Earthy',
            'slug' => 'earthy',
            'description' => 'Aroma membumi seperti tanah, akar, moss, vetiver, atau patchouli.',
        ],
        [
            'name' => 'Warm',
            'slug' => 'warm',
            'description' => 'Aroma hangat, lembut menyelimuti, dan terasa nyaman dipakai.',
        ],
        [
            'name' => 'Amber',
            'slug' => 'amber',
            'description' => 'Aroma amber, resinous, sedikit manis, hangat, dan berkesan glowing.',
        ],
        [
            'name' => 'Spicy',
            'slug' => 'spicy',
            'description' => 'Aroma rempah seperti saffron, pepper, cardamom, atau bumbu hangat.',
        ],
        [
            'name' => 'Musky',
            'slug' => 'musky',
            'description' => 'Aroma musk yang skin-like, dekat, bersih, atau lembut di kulit.',
        ],
        [
            'name' => 'Powdery',
            'slug' => 'powdery',
            'description' => 'Aroma bedak, lembut kering, halus, dan terasa rapi.',
        ],
        [
            'name' => 'Soft',
            'slug' => 'soft',
            'description' => 'Aroma lembut, nyaman, kalem, dan biasanya terasa lebih low-risk.',
        ],
    ];

    public const LEGACY_ALIASES = [
        'fresh-clean' => ['fresh', 'clean'],
        'sweet-gourmand' => ['sweet', 'gourmand'],
        'woody-earthy' => ['woody', 'earthy'],
        'warm-amber-spicy' => ['warm', 'amber', 'spicy'],
        'musky-powdery-soft' => ['musky', 'powdery', 'soft'],
    ];

    /**
     * @return array<int, string>
     */
    public static function publicSlugs(): array
    {
        return array_column(self::CATEGORIES, 'slug');
    }

    /**
     * @return array<int, string>
     */
    public static function acceptedSlugs(): array
    {
        return array_values(array_unique([
            ...self::publicSlugs(),
            ...array_keys(self::LEGACY_ALIASES),
        ]));
    }

    /**
     * @return array<int, string>
     */
    public static function filterSlugs(string $slug): array
    {
        return array_values(array_unique([
            ...(self::LEGACY_ALIASES[$slug] ?? [$slug]),
            $slug,
        ]));
    }

    /**
     * @param  array<int, string>  $tagSlugs
     */
    public static function resolvePrimarySlug(string $slug, array $tagSlugs = []): string
    {
        return match ($slug) {
            'fresh-clean' => self::hasAny($tagSlugs, ['clean', 'soapy']) ? 'clean' : 'fresh',
            'sweet-gourmand' => self::hasAny($tagSlugs, ['gourmand', 'vanilla', 'caramel', 'coffee', 'creamy']) ? 'gourmand' : 'sweet',
            'woody-earthy' => self::hasAny($tagSlugs, ['earthy', 'patchouli', 'vetiver', 'moss']) ? 'earthy' : 'woody',
            'warm-amber-spicy' => self::hasAny($tagSlugs, ['spicy', 'saffron']) ? 'spicy' : (self::hasAny($tagSlugs, ['amber']) ? 'amber' : 'warm'),
            'musky-powdery-soft' => self::hasAny($tagSlugs, ['powdery']) ? 'powdery' : (self::hasAny($tagSlugs, ['soft', 'clean', 'soapy']) ? 'soft' : 'musky'),
            default => $slug,
        };
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function tagMap(): array
    {
        return [
            'fresh' => ['citrus', 'fresh', 'aquatic'],
            'clean' => ['clean', 'soapy', 'fresh', 'musky'],
            'sweet' => ['sweet', 'fruity', 'vanilla'],
            'gourmand' => ['gourmand', 'vanilla', 'caramel', 'coffee', 'tea', 'creamy', 'sweet'],
            'floral' => ['floral', 'rose', 'jasmine', 'white-floral'],
            'woody' => ['woody', 'cedar', 'sandalwood'],
            'earthy' => ['earthy', 'vetiver', 'patchouli', 'moss'],
            'warm' => ['warm', 'amber', 'spicy'],
            'amber' => ['amber', 'warm', 'vanilla'],
            'spicy' => ['spicy', 'saffron'],
            'musky' => ['musky', 'clean', 'soft'],
            'powdery' => ['powdery', 'soft', 'musky'],
            'soft' => ['soft', 'musky', 'powdery', 'clean'],
            'fresh-clean' => ['citrus', 'fresh', 'aquatic', 'clean', 'soapy'],
            'sweet-gourmand' => ['sweet', 'gourmand', 'vanilla', 'caramel', 'coffee', 'tea', 'creamy', 'fruity'],
            'woody-earthy' => ['woody', 'earthy', 'cedar', 'sandalwood', 'vetiver', 'patchouli'],
            'warm-amber-spicy' => ['warm', 'amber', 'spicy', 'saffron'],
            'musky-powdery-soft' => ['musky', 'powdery', 'soft'],
        ];
    }

    public static function displayName(string $slug): string
    {
        foreach (self::CATEGORIES as $category) {
            if ($category['slug'] === $slug) {
                return $category['name'];
            }
        }

        return match ($slug) {
            'fresh-clean' => 'Fresh / Clean',
            'sweet-gourmand' => 'Sweet / Gourmand',
            'woody-earthy' => 'Woody / Earthy',
            'warm-amber-spicy' => 'Warm / Amber / Spicy',
            'musky-powdery-soft' => 'Musky / Powdery / Soft',
            default => $slug,
        };
    }

    /**
     * @param  array<int, string>  $tagSlugs
     * @param  array<int, string>  $needles
     */
    private static function hasAny(array $tagSlugs, array $needles): bool
    {
        return array_intersect($tagSlugs, $needles) !== [];
    }
}
