<?php

namespace Database\Seeders;

use App\Models\Note;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class NuanscentNoteEnrichmentSeeder extends Seeder
{
    private const DATASET_PATH = '/database/seeders/data/nuanscent_note_enrichment.json';

    private const OVERWRITE_EXISTING = false;

    /**
     * @var array<int, array<string, mixed>>
     */
    private array $notes = [];

    public function run(): void
    {
        $this->notes = $this->readNotes();

        $notesBySlug = Note::query()->get()->keyBy('slug');
        $notesByName = $this->buildNormalizedNameIndex($notesBySlug->values()->all());

        $updated = 0;
        $skipped = 0;
        $notFound = [];

        foreach ($this->notes as $noteData) {
            $note = $this->findNote($noteData, $notesBySlug, $notesByName);

            if (! $note) {
                $notFound[] = $noteData['slug'] ?? $noteData['name'] ?? 'tanpa-slug';

                continue;
            }

            $updates = $this->updatesFor($note, $noteData);

            if ($updates === []) {
                $skipped++;

                continue;
            }

            $note->forceFill($updates)->save();
            $updated++;
        }

        $stillMissing = Note::query()
            ->where(function ($query): void {
                $query
                    ->whereNull('note_family')
                    ->orWhere('note_family', '')
                    ->orWhereNull('description_simple')
                    ->orWhere('description_simple', '');
            })
            ->count();

        $this->command?->info('Note enrichment selesai.');
        $this->command?->info('Total JSON notes diproses: '.count($this->notes));
        $this->command?->info("Total notes diperbarui: {$updated}");
        $this->command?->info("Total notes dilewati karena sudah terisi: {$skipped}");
        $this->command?->info('Total JSON notes tidak ditemukan di DB: '.count($notFound));
        $this->command?->info("Total DB notes masih belum lengkap: {$stillMissing}");

        if ($notFound !== []) {
            $this->command?->warn('Slug/name JSON notes tidak ditemukan: '.implode(', ', $notFound));
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readNotes(): array
    {
        $path = base_path(self::DATASET_PATH);

        if (! file_exists($path)) {
            throw new RuntimeException("Dataset note enrichment tidak ditemukan di {$path}.");
        }

        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload) || ! isset($payload['notes']) || ! is_array($payload['notes'])) {
            throw new RuntimeException('Format dataset note enrichment tidak valid: key notes wajib tersedia.');
        }

        return $payload['notes'];
    }

    /**
     * @param  array<int, Note>  $notes
     * @return array<string, Note>
     */
    private function buildNormalizedNameIndex(array $notes): array
    {
        $grouped = collect($notes)->groupBy(
            fn (Note $note): string => $this->normalizeName($note->name),
        );

        return $grouped
            ->filter(fn ($items): bool => $items->count() === 1)
            ->map(fn ($items): Note => $items->first())
            ->all();
    }

    /**
     * @param  array<string, mixed>  $noteData
     * @param  \Illuminate\Support\Collection<string, Note>  $notesBySlug
     * @param  array<string, Note>  $notesByName
     */
    private function findNote(array $noteData, $notesBySlug, array $notesByName): ?Note
    {
        $slug = (string) ($noteData['slug'] ?? '');

        if ($slug !== '' && $notesBySlug->has($slug)) {
            return $notesBySlug[$slug];
        }

        $name = (string) ($noteData['name'] ?? '');
        $normalizedName = $this->normalizeName($name);

        return $normalizedName !== '' ? ($notesByName[$normalizedName] ?? null) : null;
    }

    /**
     * @param  array<string, mixed>  $noteData
     * @return array<string, string>
     */
    private function updatesFor(Note $note, array $noteData): array
    {
        $updates = [];

        foreach (['note_family', 'description_simple'] as $field) {
            $incoming = $this->cleanText($noteData[$field] ?? null);
            $current = $this->cleanText($note->{$field});

            if ($incoming === null) {
                continue;
            }

            if (self::OVERWRITE_EXISTING || $current === null) {
                if ($current !== $incoming) {
                    $updates[$field] = $incoming;
                }
            }
        }

        return $updates;
    }

    private function cleanText(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
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
}
