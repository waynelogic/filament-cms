<?php

namespace Waynelogic\FilamentCms\Filament\Settings\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Waynelogic\FilamentCms\Filament\Settings\SettingsCluster;
use Waynelogic\FilamentCms\Models\Session;
use Waynelogic\FilamentCms\System\Filament\ListSetting;

class AccessLogs extends ListSetting
{
    public function getTitle(): string|Htmlable
    {
        return __('filament-cms::cms.access-logs.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-cms::cms.access-logs.title');
    }
    public function getSubheading(): string|Htmlable|null
    {
        return __('filament-cms::cms.access-logs.subheading');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament-cms::cms.settings.group');
    }

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedLockClosed;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::LockClosed;
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->limit(10)
                    ->tooltip(fn($record) => $record->id)
                    ->wrap(),
                TextColumn::make('user.full_name')
                    ->label('Пользователь'),
                TextColumn::make('ip_address')
                    ->label('IP'),
                TextColumn::make('user_agent')
                    ->label('Браузер')
                    ->description(fn($record) => session()->id() == $record->id ? 'Текущая' : null, position: 'above')
                    ->wrap(),
                TextColumn::make('last_activity')
                    ->label('Последняя активность')
                    ->dateTime('Y-m-d H:i:s'),
                TextColumn::make('expires_at')
                    ->label('Срок действия')
                    ->dateTime('Y-m-d H:i:s'),
                IconColumn::make('isExpired')
                    ->label('Срок действия')
                    ->boolean(),
            ])
            ->defaultSort('last_activity', 'desc');
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        return Session::query();
    }
}
