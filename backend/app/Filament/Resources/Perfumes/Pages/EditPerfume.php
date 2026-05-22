<?php

namespace App\Filament\Resources\Perfumes\Pages;

use App\Filament\Resources\Perfumes\PerfumeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditPerfume extends EditRecord
{
    protected static string $resource = PerfumeResource::class;

    private const NOTE_FIELDS_BY_POSITION = [
        'top' => 'top_note_ids',
        'middle' => 'middle_note_ids',
        'base' => 'base_note_ids',
        'unspecified' => 'unspecified_note_ids',
    ];

    protected array $noteAssignments = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus parfum'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $groupedNoteIds = array_fill_keys(array_values(self::NOTE_FIELDS_BY_POSITION), []);

        DB::table('perfume_notes')
            ->where('perfume_id', $this->getRecord()->id)
            ->orderByRaw("case position when 'top' then 1 when 'middle' then 2 when 'base' then 3 else 4 end")
            ->orderBy('note_id')
            ->get(['note_id', 'position'])
            ->each(function (object $assignment) use (&$groupedNoteIds): void {
                $position = array_key_exists($assignment->position, self::NOTE_FIELDS_BY_POSITION)
                    ? $assignment->position
                    : 'unspecified';
                $field = self::NOTE_FIELDS_BY_POSITION[$position];

                $groupedNoteIds[$field][] = (int) $assignment->note_id;
            });

        foreach ($groupedNoteIds as $field => $noteIds) {
            $data[$field] = array_values(array_unique($noteIds));
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->noteAssignments = $this->normalizeGroupedNoteAssignments($data);

        foreach (self::NOTE_FIELDS_BY_POSITION as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncNoteAssignments();
        $this->getRecord()->refreshPriceRangeFromVariants();
    }

    private function syncNoteAssignments(): void
    {
        DB::table('perfume_notes')
            ->where('perfume_id', $this->getRecord()->id)
            ->delete();

        if ($this->noteAssignments === []) {
            return;
        }

        DB::table('perfume_notes')->insert(
            array_map(
                fn (array $assignment): array => [
                    'perfume_id' => $this->getRecord()->id,
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
