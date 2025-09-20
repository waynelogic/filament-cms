<?php

namespace Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\BackendUserResource;

class ListBackendUsers extends ListRecords
{
    protected static string $resource = BackendUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
