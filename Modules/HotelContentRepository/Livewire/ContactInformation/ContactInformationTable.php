<?php

namespace Modules\HotelContentRepository\Livewire\ContactInformation;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigJobDescription;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ContactInformation;
use Modules\HotelContentRepository\Models\Vendor;

class ContactInformationTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $contactableId;
    public string $contactableType;

    public function mount(int $contactableId, string $contactableType)
    {
        $this->contactableId = $contactableId;
        $this->contactableType = $contactableType;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Select::make('contactable_id')
                ->label($this->contactableType)
                ->options(function () {
                    if ($this->contactableType === 'Product') {
                        return Product::pluck('name', 'id');
                    } elseif ($this->contactableType === 'Vendor') {
                        return Vendor::pluck('name', 'id');
                    }
                    return [];
                })
                ->disabled(fn () => $this->contactableId)
                ->required(),
            Grid::make(2)
                ->schema([
                    TextInput::make('first_name')
                        ->label('First Name')
                        ->required(),
                    TextInput::make('last_name')
                        ->label('Last Name')
                        ->required(),
                ]),
            Grid::make(2)
                ->schema([
                    TextInput::make('email')
                        ->label('Email')
                        ->email(),
                    TextInput::make('phone')
                        ->label('Phone'),
                ]),
            Select::make('contactInformations')
                ->label('Job Descriptions')
                ->multiple()
                ->options(ConfigJobDescription::pluck('name', 'id')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ContactInformation::with('contactInformations')
                    ->where('contactable_id', $this->contactableId)
                    ->where('contactable_type', __NAMESPACE__ . '\\Models\\' . $this->contactableType)
            )
            ->columns([
                TextColumn::make('first_name')->label('First Name'),
                TextColumn::make('last_name')->label('Last Name'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('phone')->label('Phone'),
                TextColumn::make('contactInformations')
                    ->label('Job Descriptions')
                    ->formatStateUsing(function ($record) {
                        return $record->contactInformations->pluck('name')->join(', ');
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Contact Information')
                    ->form($this->schemeForm())
                    ->fillForm(function ($record) {
                        $data = $record->toArray();
                        $data['contactInformations'] = $record->contactInformations->pluck('id')->toArray();
                        return $data;
                    })
                    ->action(function ($data, $record) {
                        $data['contactable_type'] = __NAMESPACE__ . '\\Models\\' . $this->contactableType;
                        $contactInformations = $data['contactInformations'] ?? [];
                        unset($data['contactInformations']);
                        $record->update($data);
                        $record->contactInformations()->sync($contactInformations);
                    })
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->fillForm(function () {
                        return $this->contactableId ? ['contactable_id' => $this->contactableId] : [];
                    })
                    ->action(function ($data) {
                        if ($this->contactableId) $data['contactable_id'] = $this->contactableId;
                        $data['contactable_type'] = __NAMESPACE__ . '\\Models\\' . $this->contactableType;
                        $contactInformations = $data['contactInformations'] ?? [];
                        unset($data['contactInformations']);
                        $hotelContactInformation = ContactInformation::create($data);
                        $hotelContactInformation->contactInformations()->sync($contactInformations);
                    })
                    ->tooltip('Add New Contact Information')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.products.contact-information-table');
    }
}
