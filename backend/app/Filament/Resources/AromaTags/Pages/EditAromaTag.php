<?php

namespace App\Filament\Resources\AromaTags\Pages;

use App\Filament\Resources\AromaTags\AromaTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAromaTag extends EditRecord
{
    protected static string $resource = AromaTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus tag aroma'),
        ];
    }
}
