<?php

namespace App\Livewire\PropertyWeighting;

use App\Models\GiataProperty;
use App\Models\PropertyWeighting;
use App\Models\Supplier;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class UpdatePropertyWeighting extends Component implements HasForms
{
    use InteractsWithForms;

    /**
     * @var array|null
     */
    public ?array $data = [];

    /**
     * @var PropertyWeighting
     */
    public PropertyWeighting $record;

    /**
     * @param PropertyWeighting $weight
     * @return void
     */
    public function mount(PropertyWeighting $weight): void
    {
        $this->record = $weight;
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
                Select::make('property')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search): array => GiataProperty::select(
                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'), 'code')
                        ->where('name', 'like', "%$search%")->limit(30)->pluck('full_name', 'code')->toArray()
                    )
                    ->getOptionLabelUsing(fn($value): ?string => GiataProperty::select(
                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'))
                        ->where('code', $value)->first()->full_name)
                    ->live()
                    ->required()
                    ->unique(ignorable: $this->record),
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Supplier::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('weight')
                    ->label('Weight')
                    ->required()
                    ->maxLength(12),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    /**
     * @param ValidationException $exception
     * @return void
     */
    protected function onValidationError(ValidationException $exception): void
    {
        Notification::make()
            ->title($exception->getMessage())
            ->danger()
            ->send();
    }

    /**
     * @return RedirectResponse|Redirector
     */
    public function edit(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('weight.index');
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.property-weighting.update-property-weighting');
    }
}
