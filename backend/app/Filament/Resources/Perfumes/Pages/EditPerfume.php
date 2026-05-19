<?php

namespace App\Filament\Resources\Perfumes\Pages;

use App\Filament\Resources\Perfumes\PerfumeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditPerfume extends EditRecord
{
    protected static string $resource = PerfumeResource::class;

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
        $data['note_assignments'] = DB::table('perfume_notes')
            ->where('perfume_id', $this->getRecord()->id)
            ->orderByRaw("case position when 'top' then 1 when 'middle' then 2 when 'base' then 3 else 4 end")
            ->orderBy('note_id')
            ->get(['note_id', 'position'])
            ->map(fn (object $assignment): array => [
                'note_id' => $assignment->note_id,
                'position' => $assignment->position,
            ])
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->noteAssignments = $this->normalizeNoteAssignments($data['note_assignments'] ?? []);

        unset($data['note_assignments']);

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
