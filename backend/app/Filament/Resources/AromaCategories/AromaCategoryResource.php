<?php

namespace App\Filament\Resources\AromaCategories;

use App\Filament\Resources\AromaCategories\Pages\CreateAromaCategory;
use App\Filament\Resources\AromaCategories\Pages\EditAromaCategory;
use App\Filament\Resources\AromaCategories\Pages\ListAromaCategories;
use App\Filament\Resources\AromaCategories\Schemas\AromaCategoryForm;
use App\Filament\Resources\AromaCategories\Tables\AromaCategoriesTable;
use App\Models\AromaCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AromaCategoryResource extends Resource
{
    protected static ?string $model = AromaCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Kategori Aroma';

    protected static ?string $modelLabel = 'kategori aroma';

    protected static ?string $pluralModelLabel = 'kategori aroma';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AromaCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AromaCategoriesTable::configure($table);
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
            'index' => ListAromaCategories::route('/'),
            'create' => CreateAromaCategory::route('/create'),
            'edit' => EditAromaCategory::route('/{record}/edit'),
        ];
    }
}
