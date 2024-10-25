<?php

namespace Modules\HotelContentRepository\Livewire\TravelAgencyCommission;

use App\Helpers\ClassHelper;
use App\Models\Channel;
use App\Models\Configurations\ConfigConsortium;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
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
                            'consortia' => 'Consortia',
                            'room_type' => 'Room Type',
                        ])
                        ->live()
                        ->required()
                        ->afterStateUpdated(fn(Select $component) => $component
                            ->getContainer()
                            ->getComponent('dynamicFieldValue')
                            ->getChildComponentContainer()
                            ->fill()
                        ),
                        Grid::make()
                            ->schema(components: fn(Get $get): array => match ($get('field')) {
                                'consortia' => [
                                    Select::make('value')
                                        ->label('Consortia')
                                        ->options(ConfigConsortium::all()->pluck('name', 'id'))
                                        ->required(),
                                ],
                                'room_type' => [
                                    TextInput::make('value')
                                        ->label('Room Type')
                                        ->required(),
                                ],
                                default => []
                            })
                            ->columns(1)
                            ->columnStart(2)
                            ->key('dynamicFieldValue')
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
            ->query(TravelAgencyCommission::with('conditions'))
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
