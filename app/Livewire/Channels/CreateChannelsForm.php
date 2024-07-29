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

class CreateChannelsForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->unique()
                    ->required()
                    ->maxLength(191),
                TextInput::make('description')
                    ->required()
                    ->maxLength(191),
            ])
            ->statePath('data')
            ->model(Channel::class);
    }

    public function create(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $token = auth()->user()->createToken($data['name']);
        $data['token_id'] = $token->accessToken->id;
        $data['access_token'] = $token->plainTextToken;
        $record = Channel::create($data);

        $this->form->model($record)->saveRelationships();

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('channels.index');
    }

    public function render(): View
    {
        return view('livewire.channels.create-channels-form');
    }
}
