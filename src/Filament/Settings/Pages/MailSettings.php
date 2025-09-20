<?php

namespace Waynelogic\FilamentCms\Filament\Settings\Pages;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;
use Waynelogic\FilamentCms\Enums\Mailer;
use Waynelogic\FilamentCms\Models\MailEnv;
use Waynelogic\FilamentCms\System\Filament\EditSetting;

class MailSettings extends EditSetting
{

    public function getTitle(): string|Htmlable
    {
        return __('filament-cms::cms.mail.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-cms::cms.mail.title');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('filament-cms::cms.mail.subheading');
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __('filament-cms::cms.settings.group');
    }

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::PaperAirplane;

    public function resolveRecord(): MailEnv
    {
        return new MailEnv();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('mailer')
                ->label(__('filament-cms::cms.mail.service'))
                ->prefixIcon(Heroicon::OutlinedPaperAirplane)
                ->options(Mailer::class)
                ->required()
                ->native(false)
                ->live()
                ->default('smtp'),
            TextInput::make('scheme')
                ->label(__('filament-cms::cms.mail.scheme'))
                ->default('null'),
            TextInput::make('host')
                ->label(__('filament-cms::cms.mail.host'))
                ->prefixIcon(Heroicon::OutlinedServerStack),
            TextInput::make('port')
                ->label(__('filament-cms::cms.mail.port'))
                ->prefixIcon(Heroicon::OutlinedServerStack),
            Section::make(__('filament-cms::cms.mail.login_params'))->schema([
                TextInput::make('username')
                    ->label(__('filament-cms::cms.mail.username'))
                    ->prefixIcon(Heroicon::OutlinedUser),
                TextInput::make('password')
                    ->label(__('filament-cms::cms.mail.password'))
                    ->prefixIcon(Heroicon::OutlinedLockClosed),
            ])->columnSpanFull()
                ->columns(2)
                ->hidden(fn (Get $get): bool => $get('mailer') !== Mailer::SMTP),
            TextInput::make('from_address')
                ->label(__('filament-cms::cms.mail.from_address'))
                ->prefixIcon(Heroicon::OutlinedAtSymbol),
            TextInput::make('from_name')
                ->label(__('filament-cms::cms.mail.from_name'))
                ->prefixIcon(Heroicon::OutlinedUser),
        ]);
    }
}
