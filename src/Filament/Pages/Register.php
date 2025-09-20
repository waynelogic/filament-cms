<?php namespace Waynelogic\FilamentCms\Filament\Pages;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Facades\Filament;
use Waynelogic\FilamentCms\Models\BackendUser;

class Register extends BaseRegister
{
    public function mount(): void
    {
        parent::mount();

        if (BackendUser::query()->exists()) {
            redirect(Filament::getLoginUrl());
        }
    }
}
