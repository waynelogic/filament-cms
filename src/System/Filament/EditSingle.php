<?php

namespace Waynelogic\FilamentCms\System\Filament;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithHeaderActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Js;

/**
 * @property-read Schema $form
 */
abstract class EditSingle extends Page
{
    use InteractsWithHeaderActions;

    public ?array $data = [];

    public Model | int | string | null $record;
    public ?string $previousUrl = null;

    public function mount(): void
    {
        $this->record = $this->resolveRecord();

        $this->form->fill($this->record->attributesToArray());

        $this->previousUrl = url()->previous();
    }
    abstract public function resolveRecord();

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler($this->getSubmitFormLivewireMethodName())
                    ->footer([
                        Actions::make($this->getFormActions())
                        ->alignment($this->getFormActionsAlignment())
                        ->sticky($this->areFormActionsSticky())
                    ]),
            ]);
    }

    abstract public function form(Schema $schema): Schema;

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->columns($this->hasInlineLabels() ? 1 : 2)
            ->inlineLabel($this->hasInlineLabels())
            ->model($this->getRecord())
            ->operation('edit')
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }
    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->submit('save')
            ->action('save')
            ->keyBindings(['mod+s']);
    }

    protected function getSubmitFormAction(): Action
    {
        return $this->getSaveFormAction();
    }

    protected function getSubmitFormLivewireMethodName(): string
    {
        return 'save';
    }

    protected function getCancelFormAction(): Action
    {
        $url = $this->previousUrl;

        return Action::make('cancel')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.cancel.label'))
            ->alpineClickHandler(
                FilamentView::hasSpaMode($url)
                    ? 'document.referrer ? window.history.back() : Livewire.navigate(' . Js::from($url) . ')'
                    : 'document.referrer ? window.history.back() : (window.location.href = ' . Js::from($url) . ')',
            )
            ->color('gray');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $record = $this->getRecord();

        $record->update($data);

        Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
            ->send();
    }

    public function getRecord(): Model
    {
        return $this->record;
    }
}
