<?php

namespace App\Filament\Resources\Perfumes;

use App\Filament\Resources\Perfumes\Pages\CreatePerfume;
use App\Filament\Resources\Perfumes\Pages\EditPerfume;
use App\Filament\Resources\Perfumes\Pages\ListPerfumes;
use App\Filament\Resources\Perfumes\Schemas\PerfumeForm;
use App\Filament\Resources\Perfumes\Tables\PerfumesTable;
use App\Models\Perfume;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PerfumeResource extends Resource
{
    protected static ?string $model = Perfume::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Data Parfum';

    protected static ?string $navigationLabel = 'Parfum';

    protected static ?string $modelLabel = 'parfum';

    protected static ?string $pluralModelLabel = 'parfum';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PerfumeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerfumesTable::configure($table);
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
            'index' => ListPerfumes::route('/'),
            'create' => CreatePerfume::route('/create'),
            'edit' => EditPerfume::route('/{record}/edit'),
        ];
    }
}
