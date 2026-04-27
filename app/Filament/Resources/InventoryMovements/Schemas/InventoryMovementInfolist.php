<?php

namespace App\Filament\Resources\InventoryMovements\Schemas;

use App\Models\InventoryMovement;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class InventoryMovementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('product_id')
                    ->numeric(),
                TextEntry::make('type'),
                TextEntry::make('quantity')
                    ->numeric(),
                TextEntry::make('reason')
                    ->placeholder('-'),
                IconEntry::make('active')
                    ->boolean(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (InventoryMovement $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('user_id')
                    ->numeric()
                    ->placeholder('-'),
            ]);
    }
}
