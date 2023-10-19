<?php

namespace App\Livewire\Suppliers;

use App\Models\Suppliers;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class UpdateSuppliersForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Suppliers $record;

    public function mount (Suppliers $suppliers): void
    {
        $this->record = $suppliers;
        $this->form->fill($this->record->attributesToArray());
    }

    public function form (Form $form): Form
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

    public function edit (): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('suppliers.index');
    }

    public function render (): View
    {
        return view('livewire.suppliers.update-suppliers-form');
    }
}
