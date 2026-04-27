<?php

namespace App\Filament\Resources\InventoryMovements\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InventoryMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('reason')
                    ->default(null),
                Toggle::make('active')
                    ->required(),
                TextInput::make('user_id')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
