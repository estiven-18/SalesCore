<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\StatsOverview;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make('Todos'),
            'Activos' => Tab::make('Activos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)),
            'Inactivos' => Tab::make('Inactivos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)),
        ];
    }
}
