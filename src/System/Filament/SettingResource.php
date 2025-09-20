<?php

namespace Waynelogic\FilamentCms\System\Filament;

use Filament\Resources\Resource;
use Waynelogic\FilamentCms\Filament\Settings\SettingsCluster;

abstract class SettingResource extends Resource
{
    protected static ?string $cluster = SettingsCluster::class;
}
