<?php

namespace App\Console\Commands;

use App\Models\AromaCategory;
use App\Models\Brand;
use App\Models\Note;
use App\Models\Perfume;
use App\Support\AromaCategoryCatalog;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AuditCatalogData extends Command
{
    protected $signature = 'nuanscent:audit-catalog-data
        {--few-threshold=2 : Batas maksimum parfum published untuk kategori yang masih sangat sedikit}';

    protected $description = 'Audit read-only kualitas data katalog parfum Nuanscent.';

    public function handle(): int
    {
        $fewThreshold = max(0, (int) $this->option('few-threshold'));

        $perfumes = Perfume::query()
            ->with(['brand:id,name,slug'])
            ->withCount(['variants', 'aromaTags', 'occasions', 'notes'])
            ->orderBy('slug')
            ->get();
        $publishedPerfumes = $perfumes
            ->where('data_status', 'published')
            ->values();

        $brands = Brand::query()
            ->withCount([
                'perfumes as published_perfumes_count' => fn ($query) => $query->where('data_status', 'published'),
            ])
            ->orderBy('slug')
            ->get();

        $categories = AromaCategory::query()
            ->whereIn('slug', AromaCategoryCatalog::publicSlugs())
            ->withCount([
                'perfumes as published_perfumes_count' => fn ($query) => $query->where('data_status', 'published'),
            ])
            ->orderBy('slug')
            ->get();

        $notes = Note::query()
            ->orderBy('slug')
            ->get();

        $withoutImages = $publishedPerfumes
            ->filter(fn (Perfume $perfume): bool => $this->isBlank($perfume->image_url))
            ->values();
        $withoutPrices = $publishedPerfumes
            ->filter(fn (Perfume $perfume): bool => $perfume->price_min === null && $perfume->price_max === null)
            ->values();
        $withPartialPrices = $publishedPerfumes
            ->filter(fn (Perfume $perfume): bool => ($perfume->price_min === null) !== ($perfume->price_max === null))
            ->values();
        $withoutVariants = $publishedPerfumes
            ->where('variants_count', 0)
            ->values();
        $withoutTags = $publishedPerfumes
            ->where('aroma_tags_count', 0)
            ->values();
        $withoutOccasions = $publishedPerfumes
            ->where('occasions_count', 0)
            ->values();
        $withoutNotes = $publishedPerfumes
            ->where('notes_count', 0)
            ->values();
        $withoutCategories = $publishedPerfumes
            ->filter(fn (Perfume $perfume): bool => $perfume->main_aroma_category_id === null)
            ->values();
        $malformedImageUrls = $publishedPerfumes
            ->filter(fn (Perfume $perfume): bool => $this->isMalformedHttpUrl($perfume->image_url))
            ->values();

        $underrepresentedCategories = $categories
            ->filter(fn (AromaCategory $category): bool => $category->published_perfumes_count <= $fewThreshold)
            ->values();
        $brandsWithoutPublishedPerfumes = $brands
            ->where('published_perfumes_count', 0)
            ->values();
        $incompleteNotes = $notes
            ->filter(fn (Note $note): bool => $this->isBlank($note->note_family) || $this->isBlank($note->description_simple))
            ->values();
        $duplicateNotes = $this->duplicateNotesByNormalizedName($notes);
        $duplicatePerfumes = $this->duplicatePerfumesByNormalizedBrandAndName($perfumes);

        $this->newLine();
        $this->info('Nuanscent Catalog Data Audit');
        $this->line('Mode: read-only. Tidak ada data yang diubah.');
        $this->newLine();

        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total brand', $brands->count()],
                ['Total parfum', $perfumes->count()],
                ['Total parfum published', $publishedPerfumes->count()],
                ['Published tanpa image_url', $withoutImages->count()],
                ['Published tanpa price_min dan price_max', $withoutPrices->count()],
                ['Published dengan rentang harga parsial', $withPartialPrices->count()],
                ['Published tanpa variants', $withoutVariants->count()],
                ['Published tanpa aroma tags', $withoutTags->count()],
                ['Published tanpa occasions', $withoutOccasions->count()],
                ['Published tanpa notes', $withoutNotes->count()],
                ['Published tanpa main aroma category', $withoutCategories->count()],
                ['Published dengan image_url tampak malformed', $malformedImageUrls->count()],
                ['Note belum lengkap', $incompleteNotes->count()],
                ['Kemungkinan duplikat note', $duplicateNotes->count()],
                ['Kemungkinan duplikat parfum', $duplicatePerfumes->count()],
            ],
        );

        $this->newLine();
        $this->info('Kelengkapan parfum published');
        $this->renderPerfumeSlugs('Perlu image_url', $withoutImages);
        $this->renderPerfumeSlugs('Perlu price_min dan price_max', $withoutPrices);
        $this->renderPerfumeSlugs('Perlu review rentang harga parsial', $withPartialPrices);
        $this->renderPerfumeSlugs('Belum memiliki variants', $withoutVariants);
        $this->renderPerfumeSlugs('Perlu aroma tags', $withoutTags);
        $this->renderPerfumeSlugs('Perlu occasions', $withoutOccasions);
        $this->renderPerfumeSlugs('Perlu notes pyramid', $withoutNotes);
        $this->renderPerfumeSlugs('Perlu main aroma category', $withoutCategories);

        $this->newLine();
        $this->info("Representasi kategori aroma public (kategori dengan <= {$fewThreshold} parfum published perlu perhatian)");
        $this->table(
            ['Kategori', 'Slug', 'Published', 'Status'],
            $categories
                ->map(fn (AromaCategory $category): array => [
                    $category->name,
                    $category->slug,
                    $category->published_perfumes_count,
                    $category->published_perfumes_count === 0
                        ? 'Kosong'
                        : ($category->published_perfumes_count <= $fewThreshold ? 'Sangat sedikit' : 'Cukup'),
                ])
                ->all(),
        );

        $this->newLine();
        $this->info('Brand tanpa parfum published');
        $this->renderSimpleItems(
            $brandsWithoutPublishedPerfumes->map(fn (Brand $brand): string => "{$brand->name} ({$brand->slug})"),
        );

        $this->newLine();
        $this->info('Note belum lengkap');
        $this->renderSimpleItems(
            $incompleteNotes->map(function (Note $note): string {
                $missingFields = [];

                if ($this->isBlank($note->note_family)) {
                    $missingFields[] = 'note_family';
                }

                if ($this->isBlank($note->description_simple)) {
                    $missingFields[] = 'description_simple';
                }

                return "{$note->name} ({$note->slug}): ".implode(', ', $missingFields);
            }),
        );

        $this->newLine();
        $this->info('Kemungkinan duplikat note berdasarkan nama ternormalisasi');
        $this->renderDuplicateGroups(
            $duplicateNotes,
            fn (Note $note): string => "{$note->name} ({$note->slug})",
        );

        $this->newLine();
        $this->info('Kemungkinan duplikat parfum berdasarkan brand + nama ternormalisasi');
        $this->renderDuplicateGroups(
            $duplicatePerfumes,
            fn (Perfume $perfume): string => "{$perfume->brand?->name} / {$perfume->name} ({$perfume->slug})",
        );

        $this->newLine();
        $this->info('Image URL published yang tampak malformed');
        $this->renderSimpleItems(
            $malformedImageUrls->map(fn (Perfume $perfume): string => "{$perfume->slug}: {$perfume->image_url}"),
        );

        $this->newLine();
        $this->info('Rekomendasi tindak lanjut manual');
        $this->line('- Lengkapi image_url: '.$this->summarizePerfumeSlugs($withoutImages));
        $this->line('- Lengkapi harga: '.$this->summarizePerfumeSlugs($withoutPrices));
        $this->line('- Lengkapi notes pyramid: '.$this->summarizePerfumeSlugs($withoutNotes));
        $this->line('- Tambah data kategori: '.$this->summarizeCategories($underrepresentedCategories));
        $this->line('- Review brand tanpa parfum published: '.$this->summarizeBrands($brandsWithoutPublishedPerfumes));
        $this->line('- Review kemungkinan duplikat note: '.$this->summarizeDuplicateKeys($duplicateNotes));
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, Note>  $notes
     * @return Collection<string, Collection<int, Note>>
     */
    private function duplicateNotesByNormalizedName(Collection $notes): Collection
    {
        return $notes
            ->groupBy(fn (Note $note): string => $this->normalizeName($note->name))
            ->filter(fn (Collection $items, string $key): bool => $key !== '' && $items->count() > 1);
    }

    /**
     * @param  Collection<int, Perfume>  $perfumes
     * @return Collection<string, Collection<int, Perfume>>
     */
    private function duplicatePerfumesByNormalizedBrandAndName(Collection $perfumes): Collection
    {
        return $perfumes
            ->groupBy(function (Perfume $perfume): string {
                $brandKey = $perfume->brand?->slug ?? "brand-id-{$perfume->brand_id}";

                return $brandKey.'|'.$this->normalizeName($perfume->name);
            })
            ->filter(fn (Collection $items, string $key): bool => $key !== '' && $items->count() > 1);
    }

    /**
     * @param  Collection<int, Perfume>  $perfumes
     */
    private function renderPerfumeSlugs(string $label, Collection $perfumes): void
    {
        $this->line("<comment>{$label} ({$perfumes->count()}):</comment>");
        $this->renderSimpleItems($perfumes->pluck('slug')->sort()->values());
    }

    /**
     * @param  Collection<int, string>  $items
     */
    private function renderSimpleItems(Collection $items): void
    {
        if ($items->isEmpty()) {
            $this->line('- Tidak ada.');

            return;
        }

        foreach ($items as $item) {
            $this->line("- {$item}");
        }
    }

    /**
     * @template TItem
     * @param  Collection<string, Collection<int, TItem>>  $groups
     * @param  callable(TItem): string  $formatItem
     */
    private function renderDuplicateGroups(Collection $groups, callable $formatItem): void
    {
        if ($groups->isEmpty()) {
            $this->line('- Tidak ada.');

            return;
        }

        foreach ($groups as $key => $items) {
            $this->line("- {$key}: ".$items->map($formatItem)->implode(', '));
        }
    }

    /**
     * @param  Collection<int, Perfume>  $perfumes
     */
    private function summarizePerfumeSlugs(Collection $perfumes): string
    {
        return $perfumes->isEmpty()
            ? 'tidak ada'
            : $perfumes->pluck('slug')->sort()->implode(', ');
    }

    /**
     * @param  Collection<int, AromaCategory>  $categories
     */
    private function summarizeCategories(Collection $categories): string
    {
        return $categories->isEmpty()
            ? 'tidak ada'
            : $categories
                ->map(fn (AromaCategory $category): string => "{$category->slug} ({$category->published_perfumes_count})")
                ->implode(', ');
    }

    /**
     * @param  Collection<int, Brand>  $brands
     */
    private function summarizeBrands(Collection $brands): string
    {
        return $brands->isEmpty()
            ? 'tidak ada'
            : $brands->pluck('slug')->sort()->implode(', ');
    }

    /**
     * @param  Collection<string, mixed>  $groups
     */
    private function summarizeDuplicateKeys(Collection $groups): string
    {
        return $groups->isEmpty()
            ? 'tidak ada'
            : $groups->keys()->sort()->implode(', ');
    }

    private function normalizeName(string $name): string
    {
        return Str::of($name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }

    private function isMalformedHttpUrl(?string $url): bool
    {
        if ($this->isBlank($url)) {
            return false;
        }

        $url = trim((string) $url);
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return filter_var($url, FILTER_VALIDATE_URL) === false
            || ! in_array($scheme, ['http', 'https'], true);
    }
}
