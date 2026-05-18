<?php

namespace App\Filament\Resources\Perfumes\Tables;

use App\Models\Perfume;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PerfumesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama parfum')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('Merek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mainAromaCategory.name')
                    ->label('Kategori aroma utama')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('price_range')
                    ->label('Rentang harga')
                    ->state(fn (Perfume $record): string => self::formatPriceRange($record)),
                TextColumn::make('data_status')
                    ->label('Status data')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'published' => 'Terbit',
                        'reviewed' => 'Ditinjau',
                        default => 'Draft',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'reviewed' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('last_verified_at')
                    ->label('Terakhir diverifikasi')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('data_status')
                    ->label('Status data')
                    ->options([
                        'draft' => 'Draft',
                        'reviewed' => 'Ditinjau',
                        'published' => 'Terbit',
                    ]),
                SelectFilter::make('brand_id')
                    ->label('Merek')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('main_aroma_category_id')
                    ->label('Kategori aroma utama')
                    ->relationship('mainAromaCategory', 'name')
                    ->searchable()
                    ->preload(),
            ])
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

    private static function formatPriceRange(Perfume $record): string
    {
        $min = $record->price_min;
        $max = $record->price_max;

        if (blank($min) && blank($max)) {
            return 'Belum tersedia';
        }

        if (filled($min) && filled($max)) {
            return 'Rp ' . number_format($min, 0, ',', '.') . ' - Rp ' . number_format($max, 0, ',', '.');
        }

        if (filled($min)) {
            return 'Mulai Rp ' . number_format($min, 0, ',', '.');
        }

        return 'Hingga Rp ' . number_format($max, 0, ',', '.');
    }
}
