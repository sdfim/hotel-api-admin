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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(191),
            ])
            ->statePath('data')
            ->model(Channels::class);
    }

    public function create(): Redirector
    {
        $data = $this->form->getState();
       // dd($data);
        $token = auth()->user()->createToken($data['name']);
        $data['token_id'] = $token->accessToken->id;
        $data['access_token'] = $token->plainTextToken;
        $record = Channels::create($data);

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
