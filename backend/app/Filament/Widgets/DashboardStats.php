<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Guide;
use App\Models\Note;
use App\Models\Perfume;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Data';

    protected ?string $description = 'Pantau isi utama admin Nuanscent secara cepat.';

    protected int|array|null $columns = [
        'default' => 1,
        'md' => 2,
        'xl' => 3,
    ];

    protected function getStats(): array
    {
        return [
            Stat::make('Total merek', Brand::query()->count())
                ->description('Merek lokal yang sudah masuk admin')
                ->descriptionColor('gray')
                ->icon(Heroicon::OutlinedBuildingStorefront)
                ->color('primary'),
            Stat::make('Total parfum', Perfume::query()->count())
                ->description('Seluruh parfum yang tercatat')
                ->descriptionColor('gray')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('info'),
            Stat::make('Parfum draft', Perfume::query()->where('data_status', 'draft')->count())
                ->description('Masih perlu dilengkapi atau ditinjau')
                ->descriptionColor('gray')
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('gray'),
            Stat::make('Parfum published', Perfume::query()->where('data_status', 'published')->count())
                ->description('Siap digunakan untuk katalog publik')
                ->descriptionColor('gray')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success'),
            Stat::make('Total note parfum', Note::query()->count())
                ->description('Referensi note untuk input parfum')
                ->descriptionColor('gray')
                ->icon(Heroicon::OutlinedTag)
                ->color('warning'),
            Stat::make('Total panduan', Guide::query()->count())
                ->description('Konten edukasi dan glossary')
                ->descriptionColor('gray')
                ->icon(Heroicon::OutlinedBookOpen)
                ->color('primary'),
        ];
    }
}
