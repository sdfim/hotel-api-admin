<?php

namespace App\Livewire\PropertyWeighting;

use Livewire\Component;
use App\Models\Channels;
use App\Models\GiataProperty;
use App\Models\Weights;
use App\Models\Suppliers;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportRedirects\Redirector;

class CreatePropertyWeighting extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount (): void
    {
        $this->form->fill();
    }

    public function form (Form $form): Form
    {
        return $form
            ->schema([
                Select::make('property')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search): array => GiataProperty::select(
                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'), 'code')
                        ->where('name', 'like', "%{$search}%")->limit(30)->pluck('full_name', 'code')->toArray()
                    )
                    ->live()
                    ->required()
                    ->unique(),
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Suppliers::all()->pluck('name', 'id'))
                    ->required(),
                
                TextInput::make('weight')
                    ->label('Weight')
                    ->required()
                    ->maxLength(12),
                
            ])
            ->statePath('data')
            ->model(Weights::class);
    }

    protected function onValidationError (ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }

    public function create (): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $record = Weights::create($data);

        $this->form->model($record)->saveRelationships();

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('weight.index');
    }

    public function render (): View
    {
        return view('livewire.property-weighting.create-property-weighting');
    }
}
