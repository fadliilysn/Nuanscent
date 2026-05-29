<?php

namespace App\Filament\Resources\Notes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('note_family')
                    ->label('Keluarga note')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description_simple')
                    ->label('Deskripsi sederhana')
                    ->searchable()
                    ->limit(70)
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('note_family')
                    ->label('Keluarga note')
                    ->options(fn (): array => \App\Models\Note::query()
                        ->whereNotNull('note_family')
                        ->distinct()
                        ->orderBy('note_family')
                        ->pluck('note_family', 'note_family')
                        ->all()),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()
                    ->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus terpilih'),
                ]),
            ]);
    }
}
