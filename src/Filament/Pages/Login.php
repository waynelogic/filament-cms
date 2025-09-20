<?php namespace Waynelogic\FilamentCms\Filament\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Waynelogic\FilamentCms\Models\BackendUser;
class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (!BackendUser::query()->exists()) {
            redirect(Filament::getRegistrationUrl());
        }
    }
}
