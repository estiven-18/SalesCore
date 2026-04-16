<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('name')
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
                ActionGroup::make([
                    Action::make('deactivate')
                        ->label('Desactivate')
                        ->icon('heroicon-o-no-symbol')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn ($record): bool => ! $record->trashed() && (bool) $record->active)
                        ->action(fn ($record) => $record->update(['active' => false])),
                    DeleteAction::make()
                        ->visible(fn ($record): bool => ! $record->trashed()),
                    RestoreAction::make()
                        ->visible(fn ($record): bool => $record->trashed()),
                    ForceDeleteAction::make()
                        ->visible(fn ($record): bool => $record->trashed()),
                    Action::make('updateCategory')
                        ->label('Edit')
                        ->icon('heroicon-o-pencil-square')
                        ->visible(fn ($record): bool => ! $record->trashed())
                        ->schema([
                            TextInput::make('name')
                                ->label('Nombre')
                                ->required(),
                            Toggle::make('active')
                                ->label('Activo')
                                ->required(),
                        ])
                        ->fillForm(fn ($record): array => [
                            'name' => $record->name,
                            'active' => (bool) $record->active,
                        ])
                        ->action(function (array $data, $record): void {
                            $record->update([
                                'name' => $data['name'],
                                'active' => (bool) $data['active'],
                            ]);
                        })
                        ->stickyModalHeader(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
