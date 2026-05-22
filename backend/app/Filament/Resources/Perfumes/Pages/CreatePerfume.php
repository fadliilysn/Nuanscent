<?php

namespace App\Filament\Resources\Perfumes\Pages;

use App\Filament\Resources\Perfumes\PerfumeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePerfume extends CreateRecord
{
    protected static string $resource = PerfumeResource::class;

    private const NOTE_FIELDS_BY_POSITION = [
        'top' => 'top_note_ids',
        'middle' => 'middle_note_ids',
        'base' => 'base_note_ids',
        'unspecified' => 'unspecified_note_ids',
    ];

    protected array $noteAssignments = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->noteAssignments = $this->normalizeGroupedNoteAssignments($data);

        foreach (self::NOTE_FIELDS_BY_POSITION as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncNoteAssignments();
        $this->record->refreshPriceRangeFromVariants();
    }

    private function syncNoteAssignments(): void
    {
        DB::table('perfume_notes')
            ->where('perfume_id', $this->record->id)
            ->delete();

        if ($this->noteAssignments === []) {
            return;
        }

        DB::table('perfume_notes')->insert(
            array_map(
                fn (array $assignment): array => [
                    'perfume_id' => $this->record->id,
                    'note_id' => $assignment['note_id'],
                    'position' => $assignment['position'],
                ],
                $this->noteAssignments,
            ),
        );
    }

    private function normalizeGroupedNoteAssignments(array $data): array
    {
        $normalized = [];

        foreach (self::NOTE_FIELDS_BY_POSITION as $position => $field) {
            $noteIds = array_unique(array_filter((array) ($data[$field] ?? [])));

            foreach ($noteIds as $noteId) {
                $key = $noteId . ':' . $position;

                $normalized[$key] = [
                    'note_id' => (int) $noteId,
                    'position' => $position,
                ];
            }
        }

        return array_values($normalized);
    }
}
