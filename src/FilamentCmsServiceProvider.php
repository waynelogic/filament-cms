<?php

namespace Waynelogic\FilamentCms;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Waynelogic\FilamentCms\Console\Commands\MakeSettingCommand;
use Waynelogic\FilamentCms\Console\Commands\MakeSettingModelCommand;
use Waynelogic\FilamentCms\Console\Commands\MakeSettingPageCommand;
use Waynelogic\FilamentCms\Models\BackendUser;

class FilamentCmsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-cms';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasViews('filament-cms')
            ->hasTranslations()
            ->hasCommands([
                MakeSettingCommand::class,
                MakeSettingModelCommand::class,
                MakeSettingPageCommand::class,
            ])
            ->runsMigrations()
            ->discoversMigrations()
            ->hasRoute('web')
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->startWith(function(InstallCommand $command) {
                        $command->info(__('filament-cms::commands.install.greeting'));

                        if (Env::get('APP_URL') == 'http://localhost') {
                            $app_url = $command->ask(__('filament-cms::commands.install.set_url'));
                            Env::writeVariable('APP_URL', $app_url, '.env', true);
                            $command->call('config:clear');
                        }

                        if (Env::get('FILESYSTEM_DISK') == 'local') {
                            $change = $command->confirm(__('filament-cms::commands.install.change_filesystem'));
                            if ($change) {
                                Env::writeVariable('FILESYSTEM_DISK', 'public', '.env', true);
                                $command->call('config:clear');
                            }
                        }

                        $command->info(__('filament-cms::commands.install.linking_storage'));
                        $command->call('storage:link');

                        $command->info(__('filament-cms::commands.install.publishing'));
                        $command->call('make:notifications-table');
                        $command->call('vendor:publish', [
                            '--provider' => 'Spatie\MediaLibrary\MediaLibraryServiceProvider',
                            '--tag'      => 'medialibrary-migrations'
                        ]);
                        $command->call('vendor:publish', [
                            '--provider' => 'Spatie\MediaLibrary\MediaLibraryServiceProvider',
                            '--tag'      => 'medialibrary-config'
                        ]);
                    })
                    ->endWith(function(InstallCommand $command) {
                        $command->call('migrate');
                        $command->info(__('filament-cms::commands.install.have_a_great_day'));
                    });
            });
    }

    public function bootingPackage(): void
    {
        $this->registerBlueprints();

        $authConfig = Config::get('auth');
        $authConfig['providers'] = array_merge($authConfig['providers'], [
            'admins' => [
                'driver' => 'eloquent',
                'model' => BackendUser::class,
            ],
        ]);
        $authConfig['guards'] = array_merge($authConfig['guards'], [
            'admin' => [
                'driver' => 'session',
                'provider' => 'admins',
            ],
        ]);
        Config::set('auth', $authConfig);
    }

    private function registerBlueprints(): void
    {
        Blueprint::macro('slug', function (string $column = 'slug', bool $nullable = true) {
            return $this->string($column)->unique()->nullable($nullable);
        });

        Blueprint::macro('external_id', function (string $column = 'external_id', bool $nullable = true, $long = false) {
            if ($long) {
                return $this->string($column)->unique()->nullable($nullable);
            } else {
                return $this->addColumn('uuid', $column)->unique()->nullable($nullable);
            }
        });

        Blueprint::macro('sortable', function (string $name = 'sort_order') {
            return $this->integer($name)->default(0);
        });

        Blueprint::macro('defaultable', function (string $name = 'is_default') {
            return $this->boolean($name)->default(false);
        });

        Blueprint::macro('active', function (string $name = 'is_active', bool $default = true) {
            return $this->boolean($name)->default($default)->index();
        });
    }
}
