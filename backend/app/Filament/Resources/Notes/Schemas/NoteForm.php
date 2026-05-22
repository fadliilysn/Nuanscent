<?php

namespace App\Filament\Resources\Notes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Note')
                    ->description('Note membantu menjelaskan aroma parfum secara lebih detail di halaman publik.')
                    ->schema([
                        Fieldset::make('Identitas note')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama note')
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
                                TextInput::make('note_family')
                                    ->label('Keluarga note')
                                    ->helperText('Opsional. Contoh: citrus, floral, woody, musk.')
                                    ->maxLength(255),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                        Fieldset::make('Penjelasan')
                            ->schema([
                                Textarea::make('description_simple')
                                    ->label('Deskripsi sederhana')
                                    ->helperText('Gunakan bahasa yang mudah dipahami pemula.')
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
