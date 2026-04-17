<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Tabs\Tab;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'activate' => Tab::make('Activate')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('active', true)),
            'deactivate' => Tab::make('Deactivate')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('active', false)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
