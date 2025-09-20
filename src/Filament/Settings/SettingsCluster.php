<?php

namespace Waynelogic\FilamentCms\Filament\Settings;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class SettingsCluster extends Cluster
{
    public function getTitle(): string|Htmlable
    {
        return __('filament-cms::cms.settings.title');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament-cms::cms.settings.title');
    }
    protected static ?int $navigationSort = 9999;
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCog;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::Cog;

    protected static string $path = '/settings';
    protected Width | string | null $maxContentWidth = Width::Full;
}
