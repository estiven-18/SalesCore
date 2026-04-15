<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            
            TextInput::make('number')
                ->required(),

            Select::make('customer_id')
                ->relationship('customer', 'name')
                ->searchable()
                ->required(),

            Repeater::make('items')
                ->relationship()
                ->schema([
                    Select::make('product_id')
                        ->relationship('product', 'name')
                        ->required(),

                    TextInput::make('quantity')
                        ->numeric()
                        ->required(),

                    TextInput::make('price')
                        ->numeric()
                        ->required(),
                ])
                ->columns(3)
                ->createItemButtonLabel('Agregar producto'),

            TextInput::make('total')
                ->numeric()
                ->disabled(),
        ]);
    }
}