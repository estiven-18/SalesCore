<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable(),
                TextColumn::make('price')
                    ->sortable()
                    ->money('COP')
                    ->searchable(),
                TextColumn::make('stock')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('stock_security')
                    ->label('Security Stock')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tax_rate')
                    ->sortable()
                    ->suffix('%')
                    ->searchable(),
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(', ')
                    ->searchable()
                    ->placeholder('-'),
                IconColumn::make('active')
                    ->label('Active')
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
                    Action::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn($record): bool => ! $record->trashed() && ! (bool) $record->active)
                        ->action(fn($record) => $record->update(['active' => true])),

                    Action::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->visible(fn($record): bool => ! $record->trashed() && (bool) $record->active)
                        ->action(fn($record) => $record->update(['active' => false])),
                    EditAction::make(),

                    Action::make('adjustPrice')
                        ->label('Adjust price')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('info')
                        ->fillForm(fn($record): array => [
                            'price' => $record->price,
                        ])
                        ->form([
                            TextInput::make('price')
                                ->label('Price*')
                                ->prefix('$')
                                ->numeric()
                                ->minValue(0)
                                ->required(),
                        ])
                        ->modalHeading('Adjust price')
                        ->modalSubmitActionLabel('Save')
                        ->modalCancelActionLabel('Cancel')
                        ->action(fn($record, array $data) => $record->update([
                            'price' => $data['price'],
                        ]))->modalWidth('sm'),

                    Action::make('adjustStock')
                        ->label('Adjust stock')
                        ->color('info')
                        ->icon('heroicon-o-archive-box')
                        ->fillForm(fn($record): array => [
                            'stock' => $record->stock,
                        ])
                        ->form([
                            TextInput::make('stock')
                                ->label('Stock*')
                                ->numeric()
                                ->integer()
                                ->minValue(0)
                                ->required(),
                        ])
                        ->modalHeading('Adjust stock')
                        ->modalSubmitActionLabel('Save')
                        ->modalCancelActionLabel('Cancel')
                        ->action(fn($record, array $data) => $record->update([
                            'stock' => $data['stock'],
                        ]))->modalWidth('sm'),



                    DeleteAction::make()
                        ->before(fn($record) => $record->update(['active' => false])),
                    RestoreAction::make()
                        ->after(fn($record) => $record->update(['active' => true])),
                    ForceDeleteAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(fn($records) => $records->each(fn($record) => $record->update(['active' => false]))),
                    RestoreBulkAction::make()
                        ->after(fn($records) => $records->each(fn($record) => $record->update(['active' => true]))),

                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
