<?php

namespace App\Filament\Resources\AromaCategories\Pages;

use App\Filament\Resources\AromaCategories\AromaCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAromaCategories extends ListRecords
{
    protected static string $resource = AromaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah kategori aroma'),
        ];
    }
}
