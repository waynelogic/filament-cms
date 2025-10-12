<?php

namespace Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\BackendUserResource;

class EditBackendUser extends EditRecord
{
    protected static string $resource = BackendUserResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
