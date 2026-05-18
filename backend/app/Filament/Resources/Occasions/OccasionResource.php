<?php

namespace App\Filament\Resources\Occasions;

use App\Filament\Resources\Occasions\Pages\CreateOccasion;
use App\Filament\Resources\Occasions\Pages\EditOccasion;
use App\Filament\Resources\Occasions\Pages\ListOccasions;
use App\Filament\Resources\Occasions\Schemas\OccasionForm;
use App\Filament\Resources\Occasions\Tables\OccasionsTable;
use App\Models\Occasion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OccasionResource extends Resource
{
    protected static ?string $model = Occasion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Kegunaan';

    protected static ?string $modelLabel = 'kegunaan';

    protected static ?string $pluralModelLabel = 'kegunaan';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 50;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OccasionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OccasionsTable::configure($table);
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
            'index' => ListOccasions::route('/'),
            'create' => CreateOccasion::route('/create'),
            'edit' => EditOccasion::route('/{record}/edit'),
        ];
    }
}
