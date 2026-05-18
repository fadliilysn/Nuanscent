<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardQuickActions extends Widget
{
    protected string $view = 'filament.widgets.dashboard-quick-actions';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -10;
}
