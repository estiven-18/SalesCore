<?php

namespace App\Filament\Resources\Customers\Schemas;

use BcMath\Number;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\Column;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('GGeneral information')
                    ->schema([
                        // Primera fila
                        TextInput::make('name')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('document')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('phone')
                            ->label('Phone number')
                            ->required()
                            ->columnSpan(1),
                        // Segunda fila
                        TextInput::make('email')
                            ->label('Email address')
                            ->required()
                            ->email()
                            ->columnSpan(1),
                        TextInput::make('address')
                            ->label('Address')
                            ->required()
                            ->columnSpan(1),
                        Toggle::make('active')
                            ->default(true)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
