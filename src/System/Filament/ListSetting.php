<?php

namespace Waynelogic\FilamentCms\System\Filament;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Waynelogic\FilamentCms\Filament\Settings\SettingsCluster;

abstract class ListSetting extends Page implements HasTable
{
    use InteractsWithTable;
    protected static ?string $cluster = SettingsCluster::class;

    public function getBreadcrumbs(): array
    {
        return [
            SettingsCluster::getUrl() => SettingsCluster::getNavigationLabel(),
            url()->current() => $this->getTitle(),
        ];
    }
    public function content(Schema $schema): Schema
    {
        return $schema->schema([
            $this->table
        ]);
    }

    abstract public function table(Table $table): Table;

    protected Width | string | null $maxContentWidth = Width::Full;
}
