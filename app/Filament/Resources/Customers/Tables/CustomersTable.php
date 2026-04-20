<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions\Action;
use Filament\Infolists\Components\Section;
use Filament\Schemas\Components\Section as ComponentsSection;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('document')
                    ->searchable()
                    ->searchable(isIndividual: true),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->searchable(isIndividual: true),
                TextColumn::make('phone')
                    ->label('Phone number')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Address')
                    ->searchable(),
                IconColumn::make('active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('SendEmail')
                    ->label('Send email')
                    ->icon('heroicon-o-envelope'),

                ActionGroup::make([
                    Action::make('viewUser')
                        ->label('View customer')
                        ->icon('heroicon-o-eye')
                        ->visible(fn($record): bool => ! $record->trashed())
                        //se cambia de infolist a schema
                        ->schema([
                            TextEntry::make('name')
                                ->label('Name'),
                            TextEntry::make('document')
                                ->label('Document'),
                            TextEntry::make('phone')
                                ->label('Phone'),
                            TextEntry::make('email')
                                ->label('Email'),
                            TextEntry::make('address')
                                ->label('Address'),
                            IconEntry::make('active')
                                ->label('Active')
                                ->boolean(),
                        ])
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->slideOver()
                        ->modalwidth('sm'),

                    EditAction::make(),

                    Action::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn($record): bool => ! $record->trashed() && ! (bool) $record->active)
                        ->action(fn($record) => $record->update(['active' => true])),

                    Action::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn($record): bool => ! $record->trashed() && (bool) $record->active)
                        ->action(fn($record) => $record->update(['active' => false])),

                    DeleteAction::make()
                        ->visible(fn($record): bool => ! $record->trashed())
                        ->before(fn($record) => $record->update(['active' => false])),

                    RestoreAction::make()
                        ->visible(fn($record): bool => $record->trashed()),

                    ForceDeleteAction::make()
                        ->visible(fn($record): bool => $record->trashed()),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(fn($records) => $records->each(fn($record) => $record->update(['active' => false]))),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
