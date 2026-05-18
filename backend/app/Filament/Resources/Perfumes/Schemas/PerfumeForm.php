<?php

namespace App\Filament\Resources\Perfumes\Schemas;

use App\Models\Note;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PerfumeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Parfum')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama parfum')
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
                            ->helperText('Boleh diedit manual. Jika kosong saat nama parfum diisi, slug akan dibuat otomatis.')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('brand_id')
                            ->label('Merek')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('main_aroma_category_id')
                            ->label('Kategori aroma utama')
                            ->relationship('mainAromaCategory', 'name')
                            ->searchable()
                            ->preload(),
                        Textarea::make('short_description')
                            ->label('Deskripsi singkat')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('official_description')
                            ->label('Deskripsi resmi')
                            ->rows(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Atribut Produk')
                    ->schema([
                        TextInput::make('concentration')
                            ->label('Konsentrasi')
                            ->maxLength(255),
                        TextInput::make('volume_ml')
                            ->label('Volume (ml)')
                            ->numeric()
                            ->integer()
                            ->minValue(1),
                        TextInput::make('price_min')
                            ->label('Harga minimum')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('price_max')
                            ->label('Harga maksimum')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('image_url')
                            ->label('URL gambar')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('marketed_gender')
                            ->label('Target pemasaran')
                            ->maxLength(255),
                        TextInput::make('intensity')
                            ->label('Intensitas')
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Tag, Kegunaan, dan Note')
                    ->schema([
                        MultiSelect::make('aromaTags')
                            ->label('Tag aroma')
                            ->relationship('aromaTags', 'name')
                            ->searchable()
                            ->preload(),
                        MultiSelect::make('occasions')
                            ->label('Kegunaan')
                            ->relationship('occasions', 'name')
                            ->searchable()
                            ->preload(),
                        Repeater::make('note_assignments')
                            ->label('Note parfum')
                            ->helperText('Tambahkan note dan pilih posisinya di struktur parfum.')
                            ->schema([
                                Select::make('note_id')
                                    ->label('Note')
                                    ->options(fn (): array => Note::query()
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('position')
                                    ->label('Posisi')
                                    ->options([
                                        'top' => 'Top',
                                        'middle' => 'Middle',
                                        'base' => 'Base',
                                        'unspecified' => 'Tidak ditentukan',
                                    ])
                                    ->default('unspecified')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah note')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Sumber dan Status Data')
                    ->schema([
                        TextInput::make('source_url')
                            ->label('URL sumber')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('source_name')
                            ->label('Nama sumber')
                            ->maxLength(255),
                        DatePicker::make('last_verified_at')
                            ->label('Terakhir diverifikasi'),
                        Select::make('data_status')
                            ->label('Status data')
                            ->options([
                                'draft' => 'Draft',
                                'reviewed' => 'Ditinjau',
                                'published' => 'Terbit',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
