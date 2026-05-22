<?php

namespace App\Filament\Resources\Guides\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GuideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Panduan')
                    ->description('Konten edukasi publik untuk membantu pengguna memahami parfum dengan bahasa sederhana.')
                    ->schema([
                        Fieldset::make('Identitas artikel')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul')
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
                                    ->helperText('Boleh diedit manual. Jika kosong saat judul diisi, slug akan dibuat otomatis.')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                Select::make('status')
                                    ->label('Status')
                                    ->helperText('Hanya panduan berstatus Terbit yang tampil di halaman publik.')
                                    ->options([
                                        'draft' => 'Draft',
                                        'published' => 'Terbit',
                                    ])
                                    ->default('draft')
                                    ->required(),
                                DateTimePicker::make('published_at')
                                    ->label('Waktu terbit')
                                    ->helperText('Dipakai untuk mengurutkan panduan publik.')
                                    ->seconds(false),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        Fieldset::make('Konten panduan')
                            ->schema([
                                Textarea::make('excerpt')
                                    ->label('Ringkasan')
                                    ->helperText('Ringkasan pendek untuk kartu panduan di halaman publik.')
                                    ->rows(4)
                                    ->columnSpanFull(),
                                Textarea::make('body')
                                    ->label('Isi panduan')
                                    ->helperText('Gunakan teks biasa. Pisahkan paragraf dengan baris kosong.')
                                    ->required()
                                    ->rows(18)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
