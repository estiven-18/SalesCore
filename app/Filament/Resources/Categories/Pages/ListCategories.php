<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createCategory')
                ->label('New category')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required(),
                    Toggle::make('active')
                        ->label('Activo')
                        ->default(true)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    Category::create([
                        'name' => $data['name'],
                        'active' => (bool) $data['active'],
                    ]);
                })
                ->stickyModalHeader(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make('Todos'),
            'Activos' => Tab::make('Activos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true)),
            'Desactivos' => Tab::make('Desactivos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('active', false)),
        ];
    }
}
