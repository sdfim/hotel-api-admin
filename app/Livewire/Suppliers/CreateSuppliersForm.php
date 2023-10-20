<?php

namespace App\Livewire\Suppliers;

use Livewire\Component;
use App\Models\Suppliers;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class CreateSuppliersForm extends Component implements HasForms
{
    use InteractsWithForms;

    /**
     * @var array|null
     */
    public ?array $data = [];

    /**
     * @return void
     */
    public function mount(): void
    {
        $this->form->fill();
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
                    ->required()
                    ->maxLength(191),
                TextInput::make('description')
                    ->required()
                    ->maxLength(191),
            ])
            ->statePath('data')
            ->model(Suppliers::class);
    }

    /**
     * @return Redirector|RedirectResponse
     */
    public function create(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        $record = Suppliers::create($data);

        $this->form->model($record)->saveRelationships();

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('suppliers.index');
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.suppliers.create-suppliers-form');
    }
}
