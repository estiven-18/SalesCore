<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               
                Grid::make(1)
                    ->schema([
                        Section::make('Producto')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required(),
                                MarkdownEditor::make('description')
                                    ->label('Description')
                                    ->disableAllToolbarButtons()
                                    ->required(),
                            ])
                            ->columnSpan(1),
                        
                    ]),
                    Grid::make(1)
                            ->schema([
                                Section::make('Categorías')
                                    ->schema([
                                        Select::make('categories')
                                            ->relationship('categories', 'name')
                                            ->multiple()
                                            ->preload()
                                            ->searchable()
                                            ->required(),
                                    ]),
                                Section::make('Price and Inventory')
                                    ->schema([
                                        TextInput::make('price')
                                            ->label('Price')
                                            ->prefix('$')
                                            ->numeric()
                                            ->required(),
                                        TextInput::make('stock')
                                            ->label('Stock')
                                            ->numeric()
                                            ->required(),
                                    ]),
                            ])
                            ->columnSpan(1),
            ]);
    }
}
