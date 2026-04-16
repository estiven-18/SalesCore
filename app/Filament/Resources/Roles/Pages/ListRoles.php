<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createRole')
                ->label('New Role')
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
                    Role::create([
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
