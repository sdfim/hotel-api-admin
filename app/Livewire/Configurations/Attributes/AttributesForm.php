<?php

namespace App\Livewire\Configurations\Attributes;

use App\Enums\ConfigAttributeType;
use App\Models\Configurations\ConfigAttribute;
use App\Models\Configurations\ConfigAttributeCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class AttributesForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ConfigAttribute $record;

    public function mount(ConfigAttribute $configAttribute): void
    {
        $this->record = $configAttribute;
        $this->form->fill(
            array_merge(
                $this->record->withoutRelations()->attributesToArray(),
                ['categories' => $this->record->categories->pluck('id')->toArray()]
            )
        );
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(self::getSchema())
            ->statePath('data')
            ->model($this->record);
    }

    public static function getSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(191),

            Select::make('categories') // Use the foreign key field
                ->label('Category')
                ->multiple()
                ->native(false)
                ->options(
                    ConfigAttributeCategory::all()
                        ->sortBy('name')
                        ->pluck('name', 'id')
                        ->mapWithKeys(fn ($name, $id) => [
                            $id => \Illuminate\Support\Str::of($name)->replace('_', ' ')->title(),
                        ])
                )
                ->createOptionForm(Gate::allows('create', ConfigAttributeCategory::class) ?
                    [
                        TextInput::make('name')
                            ->label('Category Name')
                            ->required(),
                    ] : [])
                ->createOptionUsing(function (array $data) {
                    $category = ConfigAttributeCategory::create($data);
                    Notification::make()
                        ->title('Category created successfully')
                        ->success()
                        ->send();

                    return $category->id;
                }),

            Select::make('products')
                ->label('Hotels')
                ->multiple()
                ->disabled()
                ->dehydrated(false)
                ->relationship('products', 'name', fn ($query) => $query->whereNotNull('name'))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? 'Unnamed Product')
                ->searchable()
                ->loadingMessage('Loading hotels...')
                ->optionsLimit(25),

            Select::make('hotelRooms')
                ->label('Rooms')
                ->multiple()
                ->disabled()
                ->dehydrated(false)
                ->relationship('hotelRooms', 'name', fn ($query) => $query->whereNotNull('name'))
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name ?? 'Unnamed Room')
                ->searchable()
                ->loadingMessage('Loading rooms...')
                ->optionsLimit(25),
        ];
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        if (! isset($data['default_value'])) {
            $data['default_value'] = '';
        }

        // Extract categories from the form data
        $categories = $data['categories'] ?? [];
        unset($data['categories']);

        $this->record->fill($data);
        $this->record->save();

        // Sync categories
        $this->record->categories()->sync($categories);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('configurations.attributes.index');
    }

    public function render(): View
    {
        return view('livewire.configurations.attributes.attributes-form');
    }
}
