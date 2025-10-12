<?php

namespace Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\BackendUserResource;

class ListBackendUsers extends ListRecords
{
    protected static string $resource = BackendUserResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
