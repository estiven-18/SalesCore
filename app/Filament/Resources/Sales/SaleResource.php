<?php

namespace App\Filament\Resources\Sales;

use App\Filament\Resources\Sales\Pages\CreateSale;
use App\Filament\Resources\Sales\Pages\EditSale;
use App\Filament\Resources\Sales\Pages\ListSales;
use App\Filament\Resources\Sales\Pages\ViewSale;
use App\Filament\Resources\Sales\Schemas\SaleForm;
use App\Filament\Resources\Sales\Schemas\SaleInfolist;
use App\Filament\Resources\Sales\Tables\SalesTable;
use App\Models\Sale;
use BackedEnum;
use Dom\Text;
use Filament\Actions\ActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|\UnitEnum|null $navigationGroup ='Shop';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return SaleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SaleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesTable::configure($table);
        
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
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
            'view' => ViewSale::route('/{record}'),
            'edit' => EditSale::route('/{record}/edit'),
        ];
    }
}
