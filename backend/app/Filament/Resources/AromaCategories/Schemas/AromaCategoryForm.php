<?php

namespace App\Filament\Resources\AromaCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AromaCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kategori Aroma')
                    ->description('Kategori besar untuk membantu katalog, detail parfum, dan rekomendasi memberi konteks aroma.')
                    ->schema([
                        Fieldset::make('Identitas kategori')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama kategori')
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
                                    ->helperText('Jelaskan karakter kategori aroma dengan bahasa ramah pemula.')
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
