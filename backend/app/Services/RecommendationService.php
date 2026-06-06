<?php

namespace App\Services;

use App\Models\Perfume;
use App\Support\AromaCategoryCatalog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class RecommendationService
{
    private const AROMA_CATEGORY_POINTS = 25;
    private const AROMA_TAG_POINTS = 15;
    private const OCCASION_POINTS = 25;
    private const BUDGET_POINTS = 20;
    private const INTENSITY_POINTS = 10;
    private const BLIND_BUY_POINTS = 5;
    private const AVOIDED_TAG_PENALTY = 15;

    /**
     * @param  array<string, mixed>  $preferences
     * @return array<int, array<string, mixed>>
     */
    public function recommend(array $preferences): array
    {
        return $this->publishedCandidates()
            ->map(fn (Perfume $perfume): array => $this->scorePerfume($perfume, $preferences))
            ->sortByDesc([
                ['match_percentage', 'desc'],
                ['raw_score', 'desc'],
                ['perfume.name', 'asc'],
            ])
            ->take(5)
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Perfume>
     */
    private function publishedCandidates(): Collection
    {
        return Perfume::query()
            ->where('data_status', 'published')
            ->with(['brand', 'mainAromaCategory', 'aromaTags', 'occasions'])
            ->get();
    }

    /**
     * @param  array<string, mixed>  $preferences
     * @return array<string, mixed>
     */
    private function scorePerfume(Perfume $perfume, array $preferences): array
    {
        $score = 0;
        $denominator = 0;
        $matchedReasons = [];
        $dataLimitations = [];

        [$aromaScore, $aromaDenominator, $aromaReasons, $aromaLimitations] = $this->scoreAroma($perfume, $preferences);
        $score += $aromaScore;
        $denominator += $aromaDenominator;
        $matchedReasons = array_merge($matchedReasons, $aromaReasons);
        $dataLimitations = array_merge($dataLimitations, $aromaLimitations);

        [$occasionScore, $occasionDenominator, $occasionReasons, $occasionLimitations] = $this->scoreOccasion($perfume, $preferences);
        $score += $occasionScore;
        $denominator += $occasionDenominator;
        $matchedReasons = array_merge($matchedReasons, $occasionReasons);
        $dataLimitations = array_merge($dataLimitations, $occasionLimitations);

        [$budgetScore, $budgetDenominator, $budgetReasons, $budgetLimitations] = $this->scoreBudget($perfume, $preferences);
        $score += $budgetScore;
        $denominator += $budgetDenominator;
        $matchedReasons = array_merge($matchedReasons, $budgetReasons);
        $dataLimitations = array_merge($dataLimitations, $budgetLimitations);

        [$intensityScore, $intensityDenominator, $intensityReasons, $intensityLimitations] = $this->scoreIntensity($perfume, $preferences);
        $score += $intensityScore;
        $denominator += $intensityDenominator;
        $matchedReasons = array_merge($matchedReasons, $intensityReasons);
        $dataLimitations = array_merge($dataLimitations, $intensityLimitations);

        $blindBuyCaution = $this->evaluateBlindBuyCaution($perfume);
        [$blindBuyScore, $blindBuyDenominator, $blindBuyReasons] = $this->scoreBlindBuyComfort(
            $blindBuyCaution,
            (string) $preferences['blind_buy_comfort'],
        );
        $score += $blindBuyScore;
        $denominator += $blindBuyDenominator;
        $matchedReasons = array_merge($matchedReasons, $blindBuyReasons);

        [$avoidancePenalty, $avoidanceReasons] = $this->scoreAvoidedTags($perfume, $preferences);
        $score -= $avoidancePenalty;
        $matchedReasons = array_merge($matchedReasons, $avoidanceReasons);

        $score = max(0, $score);

        return [
            'perfume' => $perfume,
            'raw_score' => $score,
            'available_points' => $denominator,
            'match_percentage' => $denominator > 0 ? (int) round(($score / $denominator) * 100) : 0,
            'matched_reasons' => array_values(array_unique($matchedReasons)),
            'blind_buy_caution' => $blindBuyCaution,
            'data_limitations' => array_values(array_unique($dataLimitations)),
        ];
    }

    /**
     * @param  array<string, mixed>  $preferences
     * @return array{int, int, array<int, string>, array<int, string>}
     */
    private function scoreAroma(Perfume $perfume, array $preferences): array
    {
        $score = 0;
        $denominator = 0;
        $reasons = [];
        $limitations = [];
        $preferredCategories = $this->normalizedAromaPreferences($preferences);
        $acceptedCategorySlugs = collect($preferredCategories)
            ->flatMap(fn (string $category): array => AromaCategoryCatalog::filterSlugs($category))
            ->unique()
            ->values()
            ->all();

        if ($perfume->mainAromaCategory === null) {
            $limitations[] = 'Kategori aroma utama produk belum tersedia.';
        } else {
            $denominator += self::AROMA_CATEGORY_POINTS;

            if (in_array($perfume->mainAromaCategory->slug, $acceptedCategorySlugs, true)) {
                $score += self::AROMA_CATEGORY_POINTS;
                $reasons[] = $this->mainAromaReason($perfume->mainAromaCategory->slug, $preferredCategories);
            }
        }

        if ($perfume->aromaTags->isEmpty()) {
            $limitations[] = 'Tag aroma produk belum tersedia.';
        } else {
            $denominator += self::AROMA_TAG_POINTS;
            $supportingTags = $this->supportingTagsForCategories($preferredCategories, $perfume->aromaTags);

            if ($supportingTags->isNotEmpty()) {
                $score += min(self::AROMA_TAG_POINTS, $supportingTags->count() * 5);
                $reasons = array_merge($reasons, $this->supportingTagReasons($supportingTags, $preferredCategories));
            }
        }

        return [$score, $denominator, $reasons, $limitations];
    }

    /**
     * @param  array<string, mixed>  $preferences
     * @return array{int, int, array<int, string>, array<int, string>}
     */
    private function scoreOccasion(Perfume $perfume, array $preferences): array
    {
        if ($perfume->occasions->isEmpty()) {
            return [0, 0, [], ['Occasion produk belum tersedia.']];
        }

        $occasionSlug = (string) $preferences['occasion'];
        $matchedOccasion = $perfume->occasions->firstWhere('slug', $occasionSlug);

        if ($matchedOccasion) {
            return [
                self::OCCASION_POINTS,
                self::OCCASION_POINTS,
                ["Cocok untuk occasion {$matchedOccasion->name}."],
                [],
            ];
        }

        return [0, self::OCCASION_POINTS, [], []];
    }

    /**
     * @param  array<string, mixed>  $preferences
     * @return array{int, int, array<int, string>, array<int, string>}
     */
    private function scoreBudget(Perfume $perfume, array $preferences): array
    {
        $hasBudgetInput = ($preferences['price_min'] ?? null) !== null || ($preferences['price_max'] ?? null) !== null;

        if (! $hasBudgetInput) {
            return [0, 0, [], []];
        }

        if ($perfume->price_min === null && $perfume->price_max === null) {
            return [0, 0, [], ['Harga produk belum tersedia.']];
        }

        $userMin = ($preferences['price_min'] ?? null) !== null ? (int) $preferences['price_min'] : null;
        $userMax = ($preferences['price_max'] ?? null) !== null ? (int) $preferences['price_max'] : null;
        $perfumeMin = $perfume->price_min ?? $perfume->price_max;
        $perfumeMax = $perfume->price_max ?? $perfume->price_min;

        $matchesLowerBound = $userMin === null || $perfumeMax >= $userMin;
        $matchesUpperBound = $userMax === null || $perfumeMin <= $userMax;

        if ($matchesLowerBound && $matchesUpperBound) {
            return [
                self::BUDGET_POINTS,
                self::BUDGET_POINTS,
                ['Masuk dalam rentang budget yang dipilih.'],
                [],
            ];
        }

        return [0, self::BUDGET_POINTS, [], []];
    }

    /**
     * @param  array<string, mixed>  $preferences
     * @return array{int, int, array<int, string>, array<int, string>}
     */
    private function scoreIntensity(Perfume $perfume, array $preferences): array
    {
        $preferredIntensity = $preferences['intensity_preference'] ?? null;

        if ($preferredIntensity === null || $preferredIntensity === 'no_preference') {
            return [0, 0, [], []];
        }

        if ($perfume->intensity === null) {
            return [0, 0, [], ['Intensitas produk belum tersedia.']];
        }

        if (strtolower($perfume->intensity) === $preferredIntensity) {
            return [
                self::INTENSITY_POINTS,
                self::INTENSITY_POINTS,
                ["Intensitasnya sesuai dengan preferensi {$preferredIntensity}."],
                [],
            ];
        }

        return [0, self::INTENSITY_POINTS, [], []];
    }

    /**
     * @return array{label: string, reasons: array<int, string>}
     */
    private function evaluateBlindBuyCaution(Perfume $perfume): array
    {
        if ($perfume->mainAromaCategory === null && $perfume->aromaTags->isEmpty() && $perfume->intensity === null) {
            return [
                'label' => 'Data Belum Cukup',
                'reasons' => ['Data karakter produk masih terbatas.'],
            ];
        }

        $riskReasons = [];
        $polarizingTags = $perfume->aromaTags
            ->filter(fn ($tag): bool => $tag->is_polarizing || in_array($tag->slug, $this->knownPolarizingTagSlugs(), true))
            ->values();

        if ($polarizingTags->isNotEmpty()) {
            $riskReasons[] = 'Memiliki karakter aroma yang bisa terasa lebih spesifik: '.$polarizingTags->pluck('name')->join(', ').'.';
        }

        if ($perfume->intensity !== null && strtolower($perfume->intensity) === 'strong') {
            $riskReasons[] = 'Intensitasnya tercatat strong, jadi sebaiknya dipertimbangkan untuk blind buy.';
        }

        return match (true) {
            count($riskReasons) === 0 => [
                'label' => 'Cenderung Aman',
                'reasons' => ['Tidak ada indikator aroma polarizing dari data yang tersedia.'],
            ],
            count($riskReasons) === 1 => [
                'label' => 'Perlu Pertimbangan',
                'reasons' => $riskReasons,
            ],
            default => [
                'label' => 'Sebaiknya Coba Sample Dulu',
                'reasons' => $riskReasons,
            ],
        };
    }

    /**
     * @param  array{label: string, reasons: array<int, string>}  $caution
     * @return array{int, int, array<int, string>}
     */
    private function scoreBlindBuyComfort(array $caution, string $comfortLevel): array
    {
        $label = $caution['label'];

        $score = match ($comfortLevel) {
            'safe' => match ($label) {
                'Cenderung Aman' => 5,
                'Perlu Pertimbangan' => 2,
                default => 0,
            },
            'flexible' => match ($label) {
                'Cenderung Aman', 'Perlu Pertimbangan' => 5,
                'Sebaiknya Coba Sample Dulu' => 3,
                default => 0,
            },
            'adventurous' => $label === 'Data Belum Cukup' ? 0 : 5,
            default => 0,
        };

        if ($score > 0) {
            return [$score, self::BLIND_BUY_POINTS, ['Tingkat risiko blind buy masih sesuai dengan kenyamananmu.']];
        }

        return [0, self::BLIND_BUY_POINTS, []];
    }

    /**
     * @param  array<string, mixed>  $preferences
     * @return array{int, array<int, string>}
     */
    private function scoreAvoidedTags(Perfume $perfume, array $preferences): array
    {
        $avoidedTags = collect($preferences['avoided_tags'] ?? []);

        if ($avoidedTags->isEmpty() || $perfume->aromaTags->isEmpty()) {
            return [0, []];
        }

        $matchedAvoidedTags = $perfume->aromaTags
            ->filter(fn ($tag): bool => $avoidedTags->contains($tag->slug))
            ->values();

        if ($matchedAvoidedTags->isEmpty()) {
            return [0, []];
        }

        $penalty = min(30, $matchedAvoidedTags->count() * self::AVOIDED_TAG_PENALTY);

        return [
            $penalty,
            ['Kami menurunkan kecocokan karena kamu memilih untuk menghindari aroma '.$matchedAvoidedTags->pluck('name')->join(', ').'.'],
        ];
    }

    /**
     * @param  array<string, mixed>  $preferences
     * @return array<int, string>
     */
    private function normalizedAromaPreferences(array $preferences): array
    {
        $rawPreferences = $preferences['aroma_preferences'] ?? [$preferences['aroma_preference'] ?? null];

        return collect(is_array($rawPreferences) ? $rawPreferences : [$rawPreferences])
            ->filter(fn ($slug): bool => is_string($slug) && trim($slug) !== '')
            ->map(fn (string $slug): string => trim($slug))
            ->unique()
            ->take(3)
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $preferredCategories
     * @param  SupportCollection<int, mixed>  $tags
     * @return SupportCollection<int, mixed>
     */
    private function supportingTagsForCategories(array $preferredCategories, SupportCollection $tags): SupportCollection
    {
        $supportingSlugs = collect($preferredCategories)
            ->flatMap(fn (string $categorySlug): array => AromaCategoryCatalog::tagMap()[$categorySlug] ?? [])
            ->merge($preferredCategories)
            ->unique()
            ->values()
            ->all();

        return $tags
            ->filter(fn ($tag): bool => in_array($tag->slug, $supportingSlugs, true))
            ->unique('slug')
            ->values();
    }

    /**
     * @param  SupportCollection<int, mixed>  $tags
     * @param  array<int, string>  $preferredCategories
     * @return array<int, string>
     */
    private function supportingTagReasons(SupportCollection $tags, array $preferredCategories): array
    {
        $tagsBySlug = $tags->keyBy('slug');
        $usedTagSlugs = [];
        $reasons = [];

        foreach ($preferredCategories as $categorySlug) {
            $supportingSlugs = collect(AromaCategoryCatalog::tagMap()[$categorySlug] ?? [])
                ->merge([$categorySlug])
                ->unique()
                ->values();

            $matchedTags = $supportingSlugs
                ->map(fn (string $tagSlug) => $tagsBySlug->get($tagSlug))
                ->filter(fn ($tag): bool => $tag !== null && ! in_array($tag->slug, $usedTagSlugs, true))
                ->unique('slug')
                ->values();

            if ($matchedTags->isEmpty()) {
                continue;
            }

            $usedTagSlugs = array_merge($usedTagSlugs, $matchedTags->pluck('slug')->all());

            $nuances = $this->formatIndonesianList(
                $matchedTags
                    ->pluck('name')
                    ->map(fn (string $name): string => mb_strtolower($name))
                    ->all(),
            );

            $reasons[] = 'Nuansa pendukung yang sejalan dengan pilihan '
                .$this->categoryNameForSlug($categorySlug)
                .": {$nuances}.";
        }

        return $reasons;
    }

    /**
     * @param  array<int, string>  $preferredCategories
     */
    private function mainAromaReason(string $perfumeCategorySlug, array $preferredCategories): string
    {
        $matchedPreferences = collect($preferredCategories)
            ->filter(fn (string $categorySlug): bool => in_array(
                $perfumeCategorySlug,
                AromaCategoryCatalog::filterSlugs($categorySlug),
                true,
            ))
            ->map(fn (string $categorySlug): string => $this->categoryNameForSlug($categorySlug))
            ->unique()
            ->values()
            ->all();

        $aromaNames = $this->formatIndonesianList(
            $matchedPreferences !== []
                ? $matchedPreferences
                : [$this->categoryNameForSlug($perfumeCategorySlug)],
        );

        return "Sesuai dengan preferensi aroma {$aromaNames}.";
    }

    /**
     * @param  array<int, string>  $items
     */
    private function formatIndonesianList(array $items): string
    {
        $items = array_values($items);

        if (count($items) <= 1) {
            return $items[0] ?? '';
        }

        $lastItem = array_pop($items);

        return implode(', ', $items).' dan '.$lastItem;
    }

    private function categoryNameForSlug(string $slug): string
    {
        return AromaCategoryCatalog::displayName($slug);
    }

    /**
     * @return array<int, string>
     */
    private function knownPolarizingTagSlugs(): array
    {
        return ['oud', 'smoky', 'tobacco', 'leathery', 'incense', 'animalic'];
    }
}
