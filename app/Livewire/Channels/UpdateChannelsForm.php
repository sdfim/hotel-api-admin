<?php

namespace App\Livewire\Channels;

use App\Models\Channel;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Filament\Forms\Components\Toggle;

class UpdateChannelsForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Channel $record;

    public function mount(Channel $channel): void
    {
        $this->record = $channel;
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->unique(ignorable: $this->record)
                    ->required()
                    ->maxLength(191),
                TextInput::make('description')
                    ->required()
                    ->maxLength(191),
                Toggle::make('accept_special_params')
                    ->label('Allow Special API Parameters')
                    ->helperText('If enabled, this channel will accept "force_on_sale_on" and "force_verified_on" filters from the API.')
                    ->default(false),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('channels.index');
    }

    public function render(): View
    {
        return view('livewire.channels.update-channels-form');
    }
}
