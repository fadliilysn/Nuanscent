<?php

namespace App\Filament\Resources\Occasions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class OccasionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kegunaan')
                    ->description('Kegunaan membantu pengguna memilih parfum berdasarkan situasi pemakaian.')
                    ->schema([
                        Fieldset::make('Identitas kegunaan')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama kegunaan')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                        if (blank($get('slug'))) {
                                            $set('slug', Str::slug($state ?? ''));
                                        }
                                    }),
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->helperText('Boleh diedit manual. Jika kosong saat nama diisi, slug akan dibuat otomatis.')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        Fieldset::make('Penjelasan')
                            ->schema([
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->helperText('Jelaskan situasi pemakaian dengan singkat dan praktis.')
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
