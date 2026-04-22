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
            ->columns([
                TextColumn::make('message')
                    ->searchable(),
                
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                    
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
                    //en ingles
                    ->modalHeading('Restore Stock')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('cantidad')
                            ->label('Quantity to Restore')
                            ->numeric()
                            ->required()
                            ->minValue(fn($record) => ($record->alertable && isset($record->alertable->stock_security)) ? $record->alertable->stock_security : 1)
                            ->helperText('Must be greater than or equal to the security stock.'),
                    ])
                    ->action(function ($record, array $data) {
                        // Buscar el producto relacionado
                        $producto = $record->alertable;
                        if ($producto instanceof Product) {
                            $cantidad = (int) $data['cantidad'];
                            $stockSeguridad = (int) $producto->stock_security;
                            if ($cantidad >= $stockSeguridad) {
                                $producto->stock += $cantidad;
                                $producto->save();
                                // Eliminar la alerta después de restaurar el stock
                                $record->delete();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Invalid Quantity')
                                    ->body('The quantity must be greater than or equal to the security stock.')
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
