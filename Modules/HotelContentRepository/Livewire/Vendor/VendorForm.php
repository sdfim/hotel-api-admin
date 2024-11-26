<?php

namespace Modules\HotelContentRepository\Livewire\Vendor;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\HotelContentRepository\Models\Vendor;

class VendorForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Vendor $record;

    public function mount(Vendor $vendor = null): void
    {
        $this->record = $vendor ?? new Vendor();

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->label('Name')->required(),
                    Textarea::make('address')
                        ->label('Address')
                        ->required()
                        ->rows(5),
                    TextInput::make('lat')->label('Latitude')->required(),
                    TextInput::make('lng')->label('Longitude')->required(),
                    TextInput::make('website')->label('Website')->required(),
                ]),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $this->validate();
        $this->record->save();

        Notification::make()
            ->title('Vendor updated successfully.')
            ->livewire($this);

        session()->flash('message', 'Vendor updated successfully.');
        return redirect()->route('vendor.index');
    }

    public function save(): Redirector|RedirectResponse
    {
        $this->validate();
        $this->record->save();

        Notification::make()
            ->title('Vendor saved successfully.')
            ->livewire($this);

        session()->flash('message', 'Vendor saved successfully.');
        return redirect()->route('vendor.index');
    }

    public function render()
    {
        return view('livewire.vendors.vendor-form');
    }
}
