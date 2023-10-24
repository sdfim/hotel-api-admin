<?php

namespace App\Livewire\PropertyWeighting;

use Livewire\Component;
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
use Livewire\Features\SupportRedirects\Redirector;

class CreatePropertyWeighting extends Component implements HasForms
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
                Select::make('property')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search): array => GiataProperty::select(
                        DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name'), 'code')
                        ->where('name', 'like', "%$search%")
						->orWhere('code', $search)
						->limit(30)
						->pluck('full_name', 'code')
						->toArray()
                    )
                    ->live()
                    ->required()
                    ->unique(),
                Select::make('supplier_id')
                    ->label('Supplier')
                    ->options(Supplier::all()->pluck('name', 'id')),
                TextInput::make('weight')
                    ->label('Weight')
					->type('number')
                    ->required()
                    ->maxLength(12),

            ])
            ->statePath('data')
            ->model(PropertyWeighting::class);
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
    public function create(): RedirectResponse|Redirector
    {
        $data = $this->form->getState();

        $record = PropertyWeighting::create($data);

        $this->form->model($record)->saveRelationships();

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('property-weighting.index');
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.property-weighting.create-property-weighting');
    }
}
