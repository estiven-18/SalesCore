<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Group::make()
                    ->schema([
                        Section::make('Product information')
                            ->schema([
                                Group::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Name')
                                            ->required(),
                                        TextInput::make('price')
                                            ->label('Price')
                                            ->prefix('$')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0),
                                    ])->columns(2),

                                MarkdownEditor::make('description')
                                    ->label('Description')
                                    ->disableAllToolbarButtons()
                                    ->maxLength(100)
                                    ->required(),
                            ]),

                    ])->columnSpan(2),
                Group::make()
                    ->schema([
                        Section::make('Additional information')
                            ->schema([
                                Select::make('categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->required(),
                                Group::make()
                                    ->schema([
                                        TextInput::make('stock')
                                            ->label('Stock')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required(),
                                        TextInput::make('stock_security')
                                            ->label('Stock security')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required(),
                                    ])->columns(2),

                                TextInput::make('tax_rate')
                                    ->label('Tax rate (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                                Toggle::make('active')
                                    ->label('Active')
                                    ->default(true)
                                    ->required(),
                            ]),

                    ])->columnSpan(1),
            ])->columns(3);
    }
}
