<?php

namespace App\Filament\Resources\AromaCategories\Pages;

use App\Filament\Resources\AromaCategories\AromaCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAromaCategory extends EditRecord
{
    protected static string $resource = AromaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus kategori aroma'),
        ];
    }
}
