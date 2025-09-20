<?php namespace Waynelogic\FilamentCms\Filament\Settings\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;
use UnitEnum;
use Waynelogic\FilamentCms\Service\PackageManager;
use Waynelogic\FilamentCms\System\Filament\ListSetting;

class Packages extends ListSetting
{
    public function getTitle(): string|Htmlable
    {
        return __('filament-cms::cms.packages.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-cms::cms.packages.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('filament-cms::cms.packages.subheading');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament-cms::cms.settings.group');
    }

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedArchiveBoxArrowDown;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::ArchiveBoxArrowDown;

    public $packages;

    public function getRecords() {
        return PackageManager::instance()->all();
    }
    public function prepareRecords(): void
    {
        $this->packages = PackageManager::instance()->all();
    }

    public function checkUpdates(): void
    {

        try {
            $this->packages = PackageManager::instance()->checkUpdates()->all();

            Notification::make()
                ->success()
                ->title('✅ Проверка завершена')
                ->body('Информация об обновлениях обновлена.')
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('❌ Ошибка')
                ->body('Не удалось проверить обновления по причине: ' . $e->getMessage())
                ->send();
        }
    }
    public function table(Table $table): Table
    {
        $this->prepareRecords();

        return $table
            ->records(fn() => $this->packages)
            ->heading('Список установленных пакетов')
            ->description('Вы можете так же проверить не устарел ли компонент вашей системы.')
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-cms::cms.packages.name'))
                    ->weight(FontWeight::SemiBold)
                    ->wrap(),
                TextColumn::make('description')
                    ->label(__('filament-cms::cms.packages.description'))
                    ->wrap(),
                TextColumn::make('type')
                    ->label(__('filament-cms::cms.packages.type')),
                TextColumn::make('version')
                    ->label(__('filament-cms::cms.packages.version'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('latest')
                    ->label(__('filament-cms::cms.packages.latest'))
                    ->description(fn ($record): string => $record['latest-release-date'] ?? '-')
                    ->default('-'),
            ])
            ->headerActions([
                // TODO: Check updates
//                Action::make('check_updates')
//                    ->label('Проверить обновления')
//                    ->icon(Heroicon::OutlinedLightBulb)
//                    ->action(fn() => $this->checkUpdates())
//                    ->requiresConfirmation(),
            ]);
    }
}
