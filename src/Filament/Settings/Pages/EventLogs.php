<?php

namespace Waynelogic\FilamentCms\Filament\Settings\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Rap2hpoutre\LaravelLogViewer\LaravelLogViewer;
use Waynelogic\FilamentCms\Enums\LogLevel;
use Waynelogic\FilamentCms\System\Filament\ListSetting;

class EventLogs extends ListSetting
{
    public function getTitle(): string|Htmlable
    {
        return __('filament-cms::cms.event-logs.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-cms::cms.event-logs.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('filament-cms::cms.event-logs.subheading');
    }
    public static function getNavigationGroup(): string
    {
        return __('filament-cms::cms.settings.group');
    }

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedNumberedList;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::NumberedList;

    public Collection $records;

    public function setRecords(): void
    {
        $log_viewer = new LaravelLogViewer();
        $this->records = collect($log_viewer->all());
    }
    public function table(Table $table): Table
    {
        $this->setRecords();

        return $table
            ->records(function (int $page, int $recordsPerPage): LengthAwarePaginator {
                $records = $this->records->forPage($page, $recordsPerPage);

                return new LengthAwarePaginator(
                    $records,
                    total: $this->records->count(),
                    perPage: $recordsPerPage,
                    currentPage: $page,
                );
            })
            ->headerActions([
                $this->cleanLog()
            ])
            ->columns([
                TextColumn::make('__key')
                    ->label('ID'),
                TextColumn::make('level')
                    ->label(__('filament-cms::cms.event-logs.level'))
                    ->badge()
                    ->color(fn($record) => LogLevel::tryFrom($record['level'])->getColor()),
                TextColumn::make('context')
                    ->label(__('filament-cms::cms.event-logs.context')),
                TextColumn::make('date')
                    ->label(__('filament-cms::cms.event-logs.datetime'))
                    ->dateTime('Y-m-d H:i:s'),
                TextColumn::make('text')
                    ->label(__('filament-cms::cms.event-logs.text'))
                    ->limit(100)
                    ->wrap(),
            ])
            ->recordAction('view')
            ->recordActions([
                $this->getViewAction()
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    public function getViewAction(): Action
    {
        return Action::make('view')
            ->label('Просмотр')
            ->schema([
                Fieldset::make(__('filament-cms::cms.event-logs.data'))->schema([
                    TextEntry::make('level')
                        ->label(__('filament-cms::cms.event-logs.level'))
                        ->badge()
                        ->color(fn($record) => LogLevel::tryFrom($record['level'])->getColor()),
                    TextEntry::make('context')
                        ->label(__('filament-cms::cms.event-logs.context')),
                    TextEntry::make('date')
                        ->label(__('filament-cms::cms.event-logs.datetime'))
                        ->dateTime('Y-m-d H:i:s'),
                ])->columns(3),
                Fieldset::make(__('filament-cms::cms.event-logs.text'))->schema([
                    TextEntry::make('text')->hiddenLabel(),
                ])->columns(1),
                Fieldset::make(__('filament-cms::cms.event-logs.stack'))->schema([
                    TextEntry::make('stack')
                        ->separator("\n")
                        ->hiddenLabel()
                        ->bulleted()
                        ->limitList(6)
                        ->expandableLimitedList()
                        ->listWithLineBreaks(),
                ])->columns(1),
            ]);
    }

    private function cleanLog()
    {
        return Action::make('clear')
            ->label(__('filament-cms::cms.event-logs.clean'))
            ->requiresConfirmation()
            ->action(function () {
                $path = (new LaravelLogViewer())->pathToLogFile('laravel.log');
                app('files')->put($path, '');
                $this->resetTable();
            });
    }

}
