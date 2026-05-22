<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Merek')
                    ->description('Data dasar brand lokal yang tampil di katalog dan halaman brand publik.')
                    ->schema([
                        Fieldset::make('Identitas')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama merek')
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
                        Fieldset::make('Profil dan tautan')
                            ->schema([
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->helperText('Tulis deskripsi singkat yang faktual dan mudah dipahami.')
                                    ->rows(5)
                                    ->columnSpanFull(),
                                TextInput::make('official_website')
                                    ->label('Website resmi')
                                    ->helperText('Opsional. Gunakan URL resmi brand jika tersedia.')
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('logo_url')
                                    ->label('URL logo')
                                    ->helperText('Opsional. Gunakan URL gambar logo yang stabil.')
                                    ->url()
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
