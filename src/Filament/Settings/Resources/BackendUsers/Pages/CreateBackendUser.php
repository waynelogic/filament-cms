<?php

namespace Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\BackendUserResource;

class CreateBackendUser extends CreateRecord
{
    protected static string $resource = BackendUserResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;
}
