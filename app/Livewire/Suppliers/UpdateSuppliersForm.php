<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\Enums\TypeRequestEnum;

class UpdateSuppliersForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Supplier $record;

    public function mount(Supplier $suppliers): void
    {
        $this->record = $suppliers;
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
                Select::make('product_type')
                    ->options(
                        collect(TypeRequestEnum::cases())
                            ->mapWithKeys(fn ($enum) => [$enum->value => ucfirst($enum->value)])
                            ->toArray()
                    )
                    ->multiple()
                    ->required(),
                TextInput::make('description')
                    ->required()
                    ->maxLength(191),
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

        return redirect()->route('suppliers.index');
    }

    public function render(): View
    {
        return view('livewire.suppliers.update-suppliers-form');
    }
}
