<?php

namespace Modules\HotelContentRepository\Livewire\TravelAgencyCommission;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;

class TravelAgencyCommissionTable extends Component implements HasForms, HasTable
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
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            TextInput::make('name')
                ->label('Commission Name')
                ->required(),
            TextInput::make('commission_value')
                ->label('Commission Value')
                ->required(),
            Grid::make()->schema([
                    DatePicker::make('date_range_start')
                        ->label('Start Date')
                        ->native(false)
                        ->required(),
                    DatePicker::make('date_range_end')
                        ->label('End Date')
                        ->native(false)
                        ->required(),
                ]),

            Repeater::make('conditions')
                ->label('Related Model')
                ->schema([
                    Grid::make()->schema([
                    Select::make('field')
                        ->label('Field')
                        ->options([
                            'room_type' => 'Room Type',
                            'consortia' => 'Consortia',
                    ])
                        ->required(),
                    TextInput::make('value')
                        ->label('Value')
                        ->required(),
                    ]),

                ])
                ->createItemButtonLabel('Add Conditions')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(function () {
                $query = TravelAgencyCommission::with('conditions');
                if ($this->hotelId !== 0) {
                    $query->where('hotel_id', $this->hotelId);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Commission Name')
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('commission_value')
                    ->label('Commission Value')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('date_range_start')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->date(),
                TextColumn::make('date_range_end')
                    ->label('End Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->date(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Travel Agency Commission')
                    ->form($this->schemeForm())
                    ->fillForm(function (TravelAgencyCommission $record) {
                        $data = $record->toArray();
                        $data['conditions'] = $record->conditions->toArray();
                        return $data;
                    })
                    ->action(function (TravelAgencyCommission $record, array $data) {
                        $conditions = $data['conditions'] ?? [];
                        unset($data['conditions']);
                        $record->update($data);
                        $record->conditions()->delete();
                        foreach ($conditions as $condition) {
                            $record->conditions()->create($condition);
                        }
                        return $data;
                    }),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete Travel Agency Commission')
                    ->requiresConfirmation()
                    ->action(fn (TravelAgencyCommission $record) => $record->delete())
//                    ->visible(fn (TravelAgencyCommission $record): bool => Gate::allows('delete', $record))
                ,
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->action(function (array $data) {
                        $conditions = $data['conditions'] ?? [];
                        unset($data['conditions']);
                        $travelAgencyCommission = TravelAgencyCommission::create($data);
                        foreach ($conditions as $condition) {
                            $travelAgencyCommission->conditions()->create($condition);
                        }
                        return $data;
                    })
                    ->tooltip('Add New Travel Agency Commission')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.commissions.travel-agency-commission-table');
    }
}
