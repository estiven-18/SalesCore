<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    // esto es para decirle a Filament que este recurso se basa en el modelo User
    protected static ?string $model = User::class;

    //esto es para decirle a Filament que icono usar en la barra de navegación para este recurso

    //lista:copiar el nombre del icono (en este caso, "user") -> pegarlo aquí (en este caso, "Heroicon::OutlinedUser")
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    // esto es para decirle a Filament que atributo del modelo usar como título del registro
    // es decir, cuando Filament muestre un registro de este modelo, usará el valor del atributo 'name' como título del registro
    protected static ?string $recordTitleAttribute = 'name';

    // estos son los métodos que Filament usará para configurar el formulario, la infolist y la tabla de este recurso
    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    // este método es para configurar la infolist, que es la vista que muestra los detalles de un registro
    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    // este método es para configurar la tabla, que es la vista que muestra la lista de registros
    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    // estos métodos son para configurar las relaciones y las páginas de este recurso
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // este método es para configurar las páginas de este recurso, es decir, las rutas que Filament usará para mostrar la lista de registros, el formulario de creación, el formulario de edición y la vista de detalles
    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
