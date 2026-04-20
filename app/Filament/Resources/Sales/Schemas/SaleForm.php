<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Models\Product;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Select::make('customer_id')
                ->relationship('customer', 'name')
                ->searchable()
                ->required()
                ->createOptionForm([
                    TextInput::make('name')
                        ->required(),
                    TextInput::make('email')
                        ->required()
                        ->email(),
                    TextInput::make('document')
                        ->required(),
                    TextInput::make('phone')
                        ->required(),
                    TextInput::make('address'),
                ]),


            Repeater::make('items')
                ->relationship()
                ->schema([
                    Select::make('product_id')
                        ->relationship('product', 'name')
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $product = Product::find($state);
                            $set('unit_price', $product?->price ?? 0);
                            $set('tax_rate', $product?->tax_rate ?? 0);
                        })
                        ->required(),

                    TextInput::make('quantity')
                        ->numeric()
                        ->required()
                        ->default(1)
                        ->live()
                        ->rules([
                            function (callable $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $productId = $get('product_id');
                                    if (!$productId)
                                        return;

                                    $product = \App\Models\Product::find($productId);
                                    if ($product && (int) $value > $product->stock) {
                                        //poner en ingles
                                        $fail("Insufficient stock. Only {$product->stock} units available.");
                                    }
                                };
                            }
                        ]),

                    TextInput::make('discount')
                        ->label('Discount (%)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(100)
                        ->live(),

                    TextInput::make('tax_rate')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->live(),

                    TextInput::make('unit_price')
                        ->numeric()
                        ->required()
                        ->disabled()
                        ->live(),
                ])
                ->columns(5)
                ->live()
                ->afterStateUpdated(function (callable $set, callable $get) {
                    self::updateTotal($set, $get);
                }),

            TextInput::make('total')
                ->numeric()
                ->disabled()
                ->dehydrated()
                ->default(0)
                ->afterStateHydrated(function (callable $set, callable $get) {
                    self::updateTotal($set, $get);
                }),
        ]);
    }


    protected static function updateTotal(callable $set, callable $get): void
    {
        $items = $get('items') ?? [];

        $total = collect($items)->sum(function ($item) {
            $quantity = max(0, floatval($item['quantity'] ?? 0));
            $unitPrice = max(0, floatval($item['unit_price'] ?? 0));
            $discountRate = min(100, max(0, floatval($item['discount'] ?? 0)));
            $taxRate = max(0, floatval($item['tax_rate'] ?? 0));

            $baseAmount = $quantity * $unitPrice;
            $discountAmount = $baseAmount * ($discountRate / 100);
            $taxableAmount = max(0, $baseAmount - $discountAmount);
            $taxAmount = $taxableAmount * ($taxRate / 100);

            return $taxableAmount + $taxAmount;
        });

        $set('total', number_format($total, 2, '.', ''));
    }
}
