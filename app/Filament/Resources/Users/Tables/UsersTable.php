<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(', '),
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

                ActionGroup::make([
                    Action::make('viewUser')
                        ->label('Ver usuario')
                        ->icon('heroicon-o-eye')
                        ->visible(fn ($record): bool => ! $record->trashed())
                        ->infolist([
                            TextEntry::make('name')
                                ->label('Nombre'),
                            TextEntry::make('email')
                                ->label('Correo'),
                            TextEntry::make('roles.name')
                                ->label('Roles')
                                ->badge()
                                ->separator(', '),
                            IconEntry::make('active')
                                ->label('Activo')
                                ->boolean(),
                        ])
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->slideOver(),

                    EditAction::make(),

                    Action::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record): bool => ! $record->trashed() && ! (bool) $record->active)
                        ->action(fn ($record) => $record->update(['active' => true])),

                    Action::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn ($record): bool => ! $record->trashed() && (bool) $record->active)
                        ->action(fn ($record) => $record->update(['active' => false])),

                    DeleteAction::make()
                        ->visible(fn ($record): bool => ! $record->trashed())
                        ->before(fn ($record) => $record->update(['active' => false])),

                    RestoreAction::make()
                        ->visible(fn ($record): bool => $record->trashed()),

                    ForceDeleteAction::make()
                        ->visible(fn ($record): bool => $record->trashed()),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(fn ($records) => $records->each(fn ($record) => $record->update(['active' => false]))),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
