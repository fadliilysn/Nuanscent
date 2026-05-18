<?php

namespace App\Filament\Resources\AromaTags\Pages;

use App\Filament\Resources\AromaTags\AromaTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAromaTags extends ListRecords
{
    protected static string $resource = AromaTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah tag aroma'),
        ];
    }
}
