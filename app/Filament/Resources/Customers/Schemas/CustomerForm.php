<?php

namespace App\Filament\Resources\Customers\Schemas;

use BcMath\Number;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(), 
                TextInput::make('document')
                    ->required(),  
                TextInput::make('phone')
                    ->label('Phone number')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')    
                    ->required()
                    ->email(),
                TextInput::make('address')
                    ->label('Address')
                    ->required(),
                Toggle::make('active')
                    ->default(true)
                    ->required(),


            ]);
    }
}
