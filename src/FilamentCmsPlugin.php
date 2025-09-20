<?php

namespace Waynelogic\FilamentCms;

use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Waynelogic\FilamentCms\Filament\Pages\ManageMyProfile;
use Waynelogic\FilamentCms\Filament\Pages\Login;
use Waynelogic\FilamentCms\Filament\Pages\Register;

class FilamentCmsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-cms';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->registration(Register::class)
            ->login(Login::class)
            ->pages([
                ManageMyProfile::class
            ])
            ->discoverClusters(in: __DIR__ . '/Filament/Settings', for: 'Waynelogic\FilamentCms\Filament\Settings')
            ->userMenuItems([
                'profile' => fn (Action $action) => $action->url( ManageMyProfile::getUrl() ),
                'site' => Action::make('site')
                    ->label('Site')
                    ->url('/')
                    ->icon(Heroicon::OutlinedGlobeAlt),
            ])
            ->authGuard('admin')
            ->databaseNotifications();
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
