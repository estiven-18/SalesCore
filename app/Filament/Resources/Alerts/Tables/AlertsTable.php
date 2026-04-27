<?php

namespace App\Filament\Resources\Alerts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Models\Product;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;

class AlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('alertable.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alertable.categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(', ')
                    ->searchable()
                    ->placeholder('-'),   
                TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('message')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deleted_at')
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

                Action::make('restaurarStock')
                    ->label('Restore Stock')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->modalHeading('Restore Stock')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('cantidad')
                            ->label('Quantity to Restore')
                            ->numeric()
                            ->required()
                            ->minValue(fn($record) => ($record->alertable)
                                ? max(1, (int) $record->alertable->stock_security - (int) $record->alertable->stock)
                                : 1)
                            ->helperText(fn($record) => ($record->alertable)
                                ? 'Minimum: ' . max(1, (int) $record->alertable->stock_security - (int) $record->alertable->stock) . ' units (what is missing to reach security stock).'
                                : 'Enter quantity.')
                    ])
                    ->action(function ($record, array $data) {
                        $producto = $record->alertable;
                        if ($producto instanceof Product) {
                            $cantidad = (int) $data['cantidad'];
                            $faltante = max(1, (int) $producto->stock_security - (int) $producto->stock);

                            if ($cantidad >= $faltante) {
                                $producto->stock += $cantidad;
                                $producto->save();
                                $record->delete();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Invalid Quantity')
                                    ->body("Must restore at least {$faltante} units to reach security stock.")
                                    ->danger()
                                    ->send();
                            }
                        }
                    })
                    ->modalWidth('sm')



            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
