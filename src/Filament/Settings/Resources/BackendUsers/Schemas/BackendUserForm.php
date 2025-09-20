<?php

namespace Waynelogic\FilamentCms\Filament\Settings\Resources\BackendUsers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BackendUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Group::make([
                Section::make(__('filament-cms::admin.form.main'))->schema([
                    TextInput::make('name')
                        ->label(__('filament-cms::admin.form.first_name'))
                        ->prefixIcon(Heroicon::OutlinedArrowUturnRight)
                        ->required(),
                    TextInput::make('last_name')
                        ->label(__('filament-cms::admin.form.last_name'))
                        ->prefixIcon(Heroicon::OutlinedArrowUturnLeft),
                    TextInput::make('email')
                        ->label(__('filament-cms::admin.form.email'))
                        ->email()
                        ->required(),
                    DateTimePicker::make('email_verified_at')
                        ->label(__('filament-cms::admin.form.email_verified_at')),
                ])->columns(2)->columnSpanFull()->compact(),

                Section::make(__('filament-cms::admin.form.password'))->schema([
                    Toggle::make('change_password')
                        ->label(__('filament-cms::admin.form.change_password'))
                        ->default(false)
                        ->inline(false)
                        ->live(),
                    TextInput::make('password')
                        ->label(__('filament-cms::admin.form.password'))
                        ->password()
                        ->disabled(fn (Get $get) => !$get('change_password'))
                        ->required(fn (Get $get) => $get('change_password')),
                ])->compact()->columns(2),

                Toggle::make('is_super_admin')
                    ->label(__('filament-cms::admin.form.is_super_admin'))
                    ->required(),
            ])->columnSpan(['lg' => 2]),

            Section::make(__('filament-cms::admin.form.avatar'))->schema([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->collection('admin_avatar')
                    ->hiddenLabel(),
            ])->compact(),
        ])->columns(['lg' => 3]);
    }
}
