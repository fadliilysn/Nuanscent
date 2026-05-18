<?php

namespace App\Filament\Resources\AromaTags;

use App\Filament\Resources\AromaTags\Pages\CreateAromaTag;
use App\Filament\Resources\AromaTags\Pages\EditAromaTag;
use App\Filament\Resources\AromaTags\Pages\ListAromaTags;
use App\Filament\Resources\AromaTags\Schemas\AromaTagForm;
use App\Filament\Resources\AromaTags\Tables\AromaTagsTable;
use App\Models\AromaTag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AromaTagResource extends Resource
{
    protected static ?string $model = AromaTag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Tag Aroma';

    protected static ?string $modelLabel = 'tag aroma';

    protected static ?string $pluralModelLabel = 'tag aroma';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AromaTagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AromaTagsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAromaTags::route('/'),
            'create' => CreateAromaTag::route('/create'),
            'edit' => EditAromaTag::route('/{record}/edit'),
        ];
    }
}
