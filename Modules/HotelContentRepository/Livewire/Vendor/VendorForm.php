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
    public bool $verified;
    public $showDeleteConfirmation = false;

    public function mount(?Vendor $vendor): void
    {
        $this->record = $vendor ?? new Vendor();

        $this->verified = $vendor->verified ?? false;

        $this->form->fill($this->record->attributesToArray());
    }

    public function toggleVerified()
    {
        $this->verified = !$this->verified;
        $this->record->update(['verified' => $this->verified]);
    }

    public function confirmDeleteVendor()
    {
        $this->showDeleteConfirmation = true;
    }

    public function deleteVendor()
    {
        \DB::transaction(function () {
            foreach ($this->record->products as $product) {
                $product->related->delete();
                $product->delete();
            }
            $this->record->delete();
        });

        Notification::make()
            ->title('Vendor deleted successfully')
            ->success()
            ->send();

        $this->showDeleteConfirmation = false;

        return redirect()->route('vendor-repository.index');
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
                    TextInput::make('lat')->label('Latitude')->required()->numeric(),
                    TextInput::make('lng')->label('Longitude')->required()->numeric(),
                    TextInput::make('website')->label('Website')->required(),
                ]),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $this->validate();
        $this->record->fill($this->data);
        $this->record->verified = $this->verified ?? false;
        $isNew = !$this->record->exists;
        $this->record->save();

        $message = $isNew ? 'Vendor created successfully' : 'Vendor updated successfully';

        Notification::make()
            ->title($message)
            ->success()
            ->send();

        session()->flash('message', $message);
        return redirect()->route('vendor-repository.index');
    }

    public function render()
    {
        return view('livewire.vendors.vendor-form');
    }
}
