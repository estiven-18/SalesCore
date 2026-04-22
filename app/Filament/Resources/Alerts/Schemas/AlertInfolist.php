<?php

namespace App\Filament\Resources\Alerts\Schemas;

use App\Models\Alert;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AlertInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('alertable_type')
                    ->placeholder('-'),
                TextEntry::make('alertable_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('type'),
                TextEntry::make('message'),
                TextEntry::make('status'),
                IconEntry::make('active')
                    ->boolean(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Alert $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
