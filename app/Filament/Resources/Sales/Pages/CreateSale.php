<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    /* protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['subtotal']       = 0;
        $data['tax_total']      = 0;
        $data['discount_total'] = 0;
        $data['total']          = 0;
        $data['active']         = true;

        return $data;
    } */

    protected function afterCreate(): void
    {
        $this->record->recalculateTotalsFromItems();
    }
}
