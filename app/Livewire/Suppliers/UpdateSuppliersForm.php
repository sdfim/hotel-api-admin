<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class UpdateSuppliersForm extends Component implements HasForms
{
    use InteractsWithForms;

    /**
     * @var array|null
     */
    public ?array $data = [];

    /**
     * @var Supplier
     */
    public Supplier $record;

    /**
     * @param Supplier $suppliers
     * @return void
     */
    public function mount(Supplier $suppliers): void
    {
        $this->record = $suppliers;
        $this->form->fill($this->record->attributesToArray());
    }

    /**
     * @param Form $form
     * @return Form
     */
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
            ])
            ->statePath('data')
            ->model($this->record);
    }

    /**
     * @return Redirector|RedirectResponse
     */
    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('suppliers.index');
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.suppliers.update-suppliers-form');
    }
}
