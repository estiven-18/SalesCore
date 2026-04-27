<?php

namespace App\Filament\Resources\Alerts\Pages;

use App\Filament\Resources\Alerts\AlertResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\AlertStatsOverview;

class ListAlerts extends ListRecords
{
    protected static string $resource = AlertResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AlertStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }


}
