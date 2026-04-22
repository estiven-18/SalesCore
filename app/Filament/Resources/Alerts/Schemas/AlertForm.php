<?php

namespace App\Filament\Resources\Alerts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AlertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('alertable_type')
                    ->default(null),
                TextInput::make('alertable_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('type')
                    ->required(),
                TextInput::make('message')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
                Toggle::make('active')
                    ->required(),
            ]);
    }
}
