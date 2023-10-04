<?php

namespace App\Livewire\Suppliers;

use App\Models\Suppliers;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class CreateSuppliersForm extends Component implements HasForms
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
            ->model(Suppliers::class);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $record = Suppliers::create($data);

        $this->form->model($record)->saveRelationships();
    }

    public function render(): View
    {
        return view('livewire.suppliers.create-suppliers-form');
    }
}