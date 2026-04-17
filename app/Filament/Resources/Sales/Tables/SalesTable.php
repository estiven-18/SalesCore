<?php

namespace App\Filament\Resources\Sales\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Tables\Columns\IconColumn;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('user.name')
                    ->label('Vendedor')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('items.product.name')
                    ->label('Productos')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('-')
                    ->toggleable(),
                    
                TextColumn::make('discount_total')
                    ->label('Descuento')
                    ->money('COP')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('tax_total')
                    ->label('Impuesto')
                    ->money('COP')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('COP')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('COP')
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Sum::make()
                            ->label('Total recibido')
                            ->money('COP'),
                    ]),
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
                SelectFilter::make('user_id')
                    ->label('Vendedor')
                    ->relationship('user', 'name'),
                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name'),
                TernaryFilter::make('active')
                    ->label('Activo')
                    ->placeholder('Todos')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                 ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('activate')
                        ->label('Activar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record): bool => ! $record->trashed() && ! (bool) $record->active)
                        ->action(fn ($record) => $record->update(['active' => true])),
                    Action::make('deactivate')
                        ->label('Desactivar')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn ($record): bool => ! $record->trashed() && (bool) $record->active)
                        ->action(fn ($record) => $record->update(['active' => false])),
                    DeleteAction::make()
                        ->visible(fn ($record): bool => ! $record->trashed())
                        ->before(fn ($record) => $record->update(['active' => false])),
                    RestoreAction::make()
                        ->visible(fn ($record): bool => $record->trashed())
                        ->after(fn ($record) => $record->update(['active' => true])),
                    ForceDeleteAction::make()
                        ->visible(fn ($record): bool => $record->trashed()),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(fn ($records) => $records->each(fn ($record) => $record->update(['active' => false]))),
                    RestoreBulkAction::make()
                        ->after(fn ($records) => $records->each(fn ($record) => $record->update(['active' => true]))),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
