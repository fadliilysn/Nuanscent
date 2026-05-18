<?php

namespace App\Filament\Resources\Perfumes\Pages;

use App\Filament\Resources\Perfumes\PerfumeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreatePerfume extends CreateRecord
{
    protected static string $resource = PerfumeResource::class;

    protected array $noteAssignments = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->noteAssignments = $this->normalizeNoteAssignments($data['note_assignments'] ?? []);

        unset($data['note_assignments']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncNoteAssignments();
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

    private function normalizeNoteAssignments(array $assignments): array
    {
        $normalized = [];

        foreach ($assignments as $assignment) {
            if (blank($assignment['note_id'] ?? null)) {
                continue;
            }

            $position = $assignment['position'] ?? 'unspecified';

            if (! in_array($position, ['top', 'middle', 'base', 'unspecified'], true)) {
                $position = 'unspecified';
            }

            $key = $assignment['note_id'] . ':' . $position;

            $normalized[$key] = [
                'note_id' => (int) $assignment['note_id'],
                'position' => $position,
            ];
        }

        return array_values($normalized);
    }
}
