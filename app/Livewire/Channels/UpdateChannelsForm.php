<?php

namespace App\Livewire\Channels;

use App\Models\Channels;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Livewire\Features\SupportRedirects\Redirector;

class UpdateChannelsForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Channels $record;

    public function mount(Channels $channel): void
    {
        $this->record = $channel;
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(191),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector
    {
        $data = $this->form->getState();
        $this->record->update($data);

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();
        return redirect()->route('channels.index');
    }

    public function render(): View
    {
        return view('livewire.channels.update-channels-form');
    }
}