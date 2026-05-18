<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardQuickActions;
use App\Filament\Widgets\DashboardStats;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static bool $isDiscovered = false;

    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    public function getHeading(): string|Htmlable|null
    {
        return 'Selamat datang di Nuanscent Admin';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Ruang kerja untuk merapikan katalog parfum lokal, referensi aroma, dan konten panduan sebelum dipublikasikan.';
    }

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getWidgets(): array
    {
        return [
            DashboardQuickActions::class,
            DashboardStats::class,
        ];
    }
}
