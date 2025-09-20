<?php

namespace Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;
use Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Pages\CreateBackendUser;
use Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Pages\EditBackendUser;
use Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Pages\ListBackendUsers;
use Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Schemas\BackendUserForm;
use Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Tables\BackendUsersTable;
use Waynelogic\FilamentCms\Filament\Settings\SettingsCluster;
use Waynelogic\FilamentCms\Models\BackendUser;

class BackendUserResource extends Resource
{
    protected static ?string $model = BackendUser::class;

    protected static ?string $label = 'Администратор';
    public static function getLabel(): ?string
    {
        return __('filament-cms::admin.backend-user.label');
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-cms::admin.backend-user.plural-label');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('filament-cms::admin.group');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Users;

    protected static ?string $cluster = SettingsCluster::class;

    public static function form(Schema $schema): Schema
    {
        return BackendUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BackendUsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBackendUsers::route('/'),
            'create' => CreateBackendUser::route('/create'),
            'edit' => EditBackendUser::route('/{record}/edit'),
        ];
    }
}
