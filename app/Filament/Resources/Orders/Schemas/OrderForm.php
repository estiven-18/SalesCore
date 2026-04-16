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
                        ->required()
                        ->live(),

                    TextInput::make('price')
                        ->numeric()
                        ->required()
                        ->live(), 
                ])
                ->columns(3)
                ->live() 
                ->afterStateUpdated(function (callable $set, callable $get) {
                    self::updateTotal($set, $get);
                }),

            TextInput::make('total')
                ->numeric()
                ->disabled()
                ->dehydrated()
                ->afterStateHydrated(function (callable $set, callable $get) {
                    self::updateTotal($set, $get);
                }),
        ]);
    }

    
    protected static function updateTotal(callable $set, callable $get): void
    {
        $items = $get('items') ?? [];

        $total = collect($items)->sum(function ($item) {
            return floatval($item['quantity'] ?? 0) * floatval($item['price'] ?? 0);
        });

        $set('total', number_format($total, 2, '.', ''));
    }
}