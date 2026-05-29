<?php

namespace App\Filament\Resources\Perfumes\Schemas;

use App\Models\Note;
use App\Support\AromaCategoryCatalog;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
                Tabs::make('Form parfum')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        Tab::make('Informasi utama')
                            ->schema([
                                Section::make('Identitas parfum')
                                    ->description('Data dasar yang dipakai untuk katalog publik dan pencarian admin.')
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
                                            ->helperText('Kategori utama membantu katalog dan rekomendasi memberi konteks aroma.')
                                            ->relationship(
                                                'mainAromaCategory',
                                                'name',
                                                fn ($query) => $query->whereIn('slug', AromaCategoryCatalog::publicSlugs()),
                                            )
                                            ->searchable()
                                            ->preload(),
                                        Select::make('data_status')
                                            ->label('Status data')
                                            ->helperText('Hanya data berstatus Terbit yang tampil di halaman publik.')
                                            ->options([
                                                'draft' => 'Draft',
                                                'reviewed' => 'Ditinjau',
                                                'published' => 'Terbit',
                                            ])
                                            ->default('draft')
                                            ->required(),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Deskripsi & sumber')
                            ->schema([
                                Section::make('Deskripsi')
                                    ->description('Tulis ringkasan ramah pemula dan deskripsi resmi dari sumber data.')
                                    ->schema([
                                        Textarea::make('short_description')
                                            ->label('Deskripsi singkat')
                                            ->helperText('Ringkasan pendek untuk kartu katalog dan preview.')
                                            ->rows(4)
                                            ->columnSpanFull(),
                                        Textarea::make('official_description')
                                            ->label('Deskripsi resmi')
                                            ->helperText('Isi sesuai sumber resmi atau referensi yang sudah diverifikasi.')
                                            ->rows(8)
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Sumber data')
                                    ->description('Catat asal data agar setiap parfum tetap bisa ditelusuri.')
                                    ->schema([
                                        TextInput::make('source_name')
                                            ->label('Nama sumber')
                                            ->placeholder('Contoh: Website resmi brand')
                                            ->maxLength(255),
                                        TextInput::make('source_url')
                                            ->label('URL sumber')
                                            ->helperText('Gunakan URL halaman produk atau referensi utama jika tersedia.')
                                            ->url()
                                            ->maxLength(255),
                                        DatePicker::make('last_verified_at')
                                            ->label('Terakhir diverifikasi')
                                            ->helperText('Tanggal terakhir data parfum dicek ulang.'),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Detail produk')
                            ->schema([
                                Section::make('Atribut produk')
                                    ->schema([
                                        TextInput::make('concentration')
                                            ->label('Konsentrasi')
                                            ->placeholder('Contoh: EDP, EDT, extrait')
                                            ->maxLength(255),
                                        TextInput::make('marketed_gender')
                                            ->label('Target pemasaran')
                                            ->placeholder('Contoh: unisex, pria, wanita')
                                            ->maxLength(255),
                                        TextInput::make('intensity')
                                            ->label('Intensitas')
                                            ->placeholder('Contoh: soft, medium, strong')
                                            ->maxLength(255),
                                        TextInput::make('volume_ml')
                                            ->label('Volume legacy (ml)')
                                            ->helperText('Dipakai hanya jika parfum tidak memiliki data varian ukuran.')
                                            ->numeric()
                                            ->integer()
                                            ->minValue(1),
                                    ])
                                    ->columns(2),
                                Section::make('Rentang harga')
                                    ->description('Jika varian produk diisi, harga minimum dan maksimum akan diperbarui otomatis dari harga varian setelah disimpan.')
                                    ->schema([
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
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Varian produk')
                            ->schema([
                                Section::make('Varian produk')
                                    ->description('Gunakan untuk pilihan ukuran atau harga pembelian dalam satu parfum yang sama.')
                                    ->schema([
                                        Repeater::make('variants')
                                            ->label('Varian produk')
                                            ->relationship('variants')
                                            ->schema([
                                                TextInput::make('label')
                                                    ->label('Label')
                                                    ->placeholder('Contoh: Travel size')
                                                    ->maxLength(255),
                                                TextInput::make('volume_ml')
                                                    ->label('Volume (ml)')
                                                    ->numeric()
                                                    ->integer()
                                                    ->minValue(1),
                                                TextInput::make('price')
                                                    ->label('Harga')
                                                    ->numeric()
                                                    ->integer()
                                                    ->minValue(0)
                                                    ->prefix('Rp'),
                                            ])
                                            ->columns(3)
                                            ->itemLabel(fn (?array $state): ?string => filled($state['label'] ?? null)
                                                ? $state['label']
                                                : null)
                                            ->addActionLabel('Tambah varian')
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Profil aroma')
                            ->schema([
                                Section::make('Tag dan occasion')
                                    ->description('Hubungkan parfum dengan tag aroma dan konteks pemakaian yang relevan.')
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
                                    ])
                                    ->columns(2),
                                Section::make('Notes pyramid')
                                    ->description('Pilih beberapa note sekaligus untuk setiap posisi pyramid.')
                                    ->schema([
                                        MultiSelect::make('top_note_ids')
                                            ->label('Top Notes')
                                            ->helperText('Pilih beberapa note yang termasuk top notes.')
                                            ->options(fn (): array => Note::query()
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all())
                                            ->searchable()
                                            ->preload(),
                                        MultiSelect::make('middle_note_ids')
                                            ->label('Middle Notes')
                                            ->helperText('Pilih beberapa note yang menjadi karakter utama parfum.')
                                            ->options(fn (): array => Note::query()
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all())
                                            ->searchable()
                                            ->preload(),
                                        MultiSelect::make('base_note_ids')
                                            ->label('Base Notes')
                                            ->helperText('Pilih beberapa note yang terasa paling lama atau menjadi fondasi parfum.')
                                            ->options(fn (): array => Note::query()
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all())
                                            ->searchable()
                                            ->preload(),
                                        MultiSelect::make('unspecified_note_ids')
                                            ->label('Notes tanpa posisi')
                                            ->helperText('Gunakan Notes tanpa posisi jika sumber tidak menyebut top/middle/base.')
                                            ->options(fn (): array => Note::query()
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all())
                                            ->searchable()
                                            ->preload(),
                                    ])
                                    ->columns(2),
                            ]),
                        Tab::make('Media')
                            ->schema([
                                Section::make('Gambar produk')
                                    ->description('Gunakan URL gambar botol atau produk. Jika kosong, halaman publik memakai fallback initial.')
                                    ->schema([
                                        Fieldset::make('URL gambar')
                                            ->schema([
                                                TextInput::make('image_url')
                                                    ->label('URL gambar')
                                                    ->helperText('Tempel URL gambar produk yang stabil dan bisa diakses publik.')
                                                    ->url()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
