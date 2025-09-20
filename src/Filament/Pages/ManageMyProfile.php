<?php

namespace Waynelogic\FilamentCms\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithHeaderActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Waynelogic\FilamentCms\Models\BackendUser;
use Waynelogic\FilamentCms\System\Filament\EditSingle;

/**
 * @property-read Schema $form
 */
class ManageMyProfile extends EditSingle
{
    use InteractsWithHeaderActions;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedUser;
    protected static string | BackedEnum | null $activeNavigationIcon = Heroicon::User;
    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()?->full_name . ' - ' . __('filament-cms::admin.my-profile');
    }

    public function resolveRecord(): ?BackendUser
    {
        return BackendUser::query()->where('id', auth()->user()->id)->first();
    }

    public function form(Schema $schema): Schema
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
                        ->required()
                        ->columnSpanFull(),
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
            ])->columnSpan(['lg' => 2]),


            Section::make(__('filament-cms::admin.form.avatar'))->schema([
                SpatieMediaLibraryFileUpload::make('avatar')
                    ->collection('admin_avatar')
                    ->hiddenLabel(),
            ])->compact(),

            // TODO: SETTINGS
        ])->columns(['lg' => 3]);
    }
}
