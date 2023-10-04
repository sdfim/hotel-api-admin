<?php

namespace App\Livewire\Channels;

use App\Models\Channels;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;

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
                Forms\Components\TextInput::make('token_id')
                    ->required()
                    ->numeric(),
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

    public function create(): void
    {
        $data = $this->form->getState();

        $record = Channels::create($data);

        $this->form->model($record)->saveRelationships();
    }

    public function render(): View
    {
        return view('livewire.channels.create-channels-form');
    }
}