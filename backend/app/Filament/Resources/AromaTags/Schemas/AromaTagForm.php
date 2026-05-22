<?php

namespace App\Filament\Resources\AromaTags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AromaTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tag Aroma')
                    ->description('Tag aroma dipakai untuk detail parfum, filter, dan logika rekomendasi.')
                    ->schema([
                        Fieldset::make('Identitas tag')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama tag')
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
                                Toggle::make('is_polarizing')
                                    ->label('Cenderung polarizing')
                                    ->helperText('Aktifkan untuk aroma yang biasanya lebih berisiko untuk blind buy.')
                                    ->default(false),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                        Fieldset::make('Penjelasan')
                            ->schema([
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->helperText('Jelaskan nuansa aroma secara singkat dan mudah dipahami.')
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
