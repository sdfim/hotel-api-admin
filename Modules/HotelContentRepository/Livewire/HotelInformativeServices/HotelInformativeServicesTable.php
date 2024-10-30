<?php

namespace Modules\HotelContentRepository\Livewire\HotelInformativeServices;


use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigServiceType;
use Filament\Forms\Components\Select;
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
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelInformativeService;

class HotelInformativeServicesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $hotelId;

    public function mount(int $hotelId)
    {
        $this->hotelId = $hotelId;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            Select::make('hotel_id')
                ->label('Hotel')
                ->options(Hotel::pluck('name', 'id'))
                ->disabled(fn () => $this->hotelId)
                ->required(),
            Select::make('service_id')
                ->label('Service Type')
                ->options(ConfigServiceType::all()->pluck('name', 'id')->map(function ($name, $id) {
                    $serviceType = ConfigServiceType::find($id);
                    return $name . ' (' . $serviceType->cost . ')';
                }))
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                HotelInformativeService::with('service')->where('hotel_id', $this->hotelId)
            )
            ->columns([
                TextColumn::make('service.name')->label('Service Type')->searchable(),
                TextColumn::make('service.description')->label('Description')->searchable(),
                TextColumn::make('service.cost')->label('Cost')->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Service')
                    ->form($this->schemeForm()),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->fillForm(function () {
                        return $this->hotelId ? ['hotel_id' => $this->hotelId] : [];
                    })
                    ->action(function ($data) {
                        if ($this->hotelId) $data['hotel_id'] = $this->hotelId;
                        HotelInformativeService::create($data);
                    })
                    ->tooltip('Add New Service')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-informative-services-table');
    }
}
