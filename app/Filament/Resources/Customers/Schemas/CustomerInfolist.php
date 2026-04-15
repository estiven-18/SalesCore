<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Name'),
                TextEntry::make('document')
                    ->label('Document'),
                TextEntry::make('phone')
                    ->label('Phone number'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('address')
                    ->label('Address'),
                IconEntry::make('active')
                    ->label('Active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Updated at')
                    ->dateTime()
                    ->placeholder('-'), 
            ]);
    }
}
