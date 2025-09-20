<?php

namespace Waynelogic\FilamentCms\System\Filament;

use Filament\Pages\Concerns\InteractsWithHeaderActions;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Waynelogic\FilamentCms\Filament\Settings\SettingsCluster;

/**
 * @property-read Schema $form
 */
abstract class EditSetting extends EditSingle
{
    use InteractsWithHeaderActions;
    protected static ?string $cluster = SettingsCluster::class;

    public function getBreadcrumbs(): array
    {
        return [
            SettingsCluster::getUrl() => SettingsCluster::getNavigationLabel(),
            url()->current() => $this->getTitle(),
        ];
    }

    public function resolveRecord()
    {
        return $this->model::instance();
    }

    protected Width | string | null $maxContentWidth = Width::Full;
}
