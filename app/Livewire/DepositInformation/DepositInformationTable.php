<?php

namespace App\Livewire\DepositInformation;

use App\Actions\DepositInformation\AddDepositInformation;
use App\Actions\DepositInformation\EditDepositInformation;
use App\Helpers\ClassHelper;
use App\Models\DepositInformation;
use App\Models\Property;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\HotelContentRepository\Livewire\HasProductActions;
use Modules\HotelContentRepository\Models\HotelRate;

class DepositInformationTable extends Component implements HasForms, HasTable
{
    use DepositFieldTrait;
    use HasProductActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $giataCode = 0;

    public ?int $rateId = null;

    public string $title;

    public function mount(?Property $giata = null, ?int $rateId = null)
    {
        $this->giataCode = $giata ? $giata->code : 0;
        $this->rateId = $rateId;
//        $rate = HotelRate::where('id', $rateId)->first();
        $this->title = 'Deposit Information for '.($giata ? $giata->name : 'Unknown Property');
        if ($this->rateId) {
            $this->title .= ' - Rate ID: '.$this->rateId;
//            $this->title .= ' - Rate Name: '.$rate->name;
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(DepositInformation::query())
            ->modifyQueryUsing(function (Builder $query) {
                if ($this->rateId) {
                    $query->where(function ($q) {
                        $q->where('rate_id', $this->rateId)
                            ->orWhereNull('rate_id');
                    });
                } else {
                    $query->whereNull('rate_id');
                }
                if ($this->giataCode && $this->giataCode !== 0) {
                    $query->whereHas('giata', function ($query) {
                        $query->where('code', $this->giataCode);
                    });
                }
            })
            ->deferLoading()
            ->columns([
//                TextColumn::make('level')
//                    ->label('Level')
//                    ->badge()
//                    ->getStateUsing(function ($record) {
//                        return ($this->giataCode && $this->rateId && $this->rateId === $record->rate_id) ? 'Rate' : 'Hotel';
//                    })
//                    ->colors([
//                        'primary' => 'Hotel',
//                        'warning' => 'Rate',
//                    ]),
                TextColumn::make('giata.name')->label('Hotel')->wrap()->searchable(),
                TextColumn::make('giata_code')->label('Giata Code')->searchable(),
                TextColumn::make('name')->label('Name')->wrap()->searchable(),
                TextColumn::make('start_date')->label('Travel Start Date')->date()->searchable(),
                TextColumn::make('expiration_date')
                    ->label('Travel End Date')
                    ->date()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        $date = Carbon::parse($state)->format('M j, Y');

                        return $date === 'Feb 2, 2112' ? '' : $date;
                    }),
                TextColumn::make('manipulable_price_type')
                    ->label('Price Type')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('price_value_type')
                    ->label('Value Type')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('price_value')->label('Value')->searchable(),
                TextColumn::make('price_value_target')
                    ->label('Value Target')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->tooltip('Edit Deposit Information')
                        ->form(fn ($record) => $this->schemeForm($record, true))
                        ->fillForm(function ($record) {
                            $data = $record->toArray();
                            $data['conditions'] = $record->conditions->toArray();

                            return $data;
                        })
                        ->action(function (array $data, DepositInformation $record) {
                            if ($this->giataCode) {
                                $data['product_id'] = $this->giataCode;
                            }
                            if (! $data['expiration_date']) {
                                $data['expiration_date'] = Carbon::create(2112, 02, 02);
                            }
                            /* @var EditDepositInformation $editDepositInformation */
                            $editDepositInformation = app(EditDepositInformation::class);
                            $editDepositInformation->updateWithConditions($record, $data);
                        })
                        ->modalWidth('7xl')
                        ->visible(fn () => Gate::allows('create', DepositInformation::class)),
                    DeleteAction::make()
                        ->tooltip('Delete Deposit Information')
                        ->action(function (DepositInformation $record) {
                            $record->delete();
                        })
                        ->visible(fn () => Gate::allows('create', DepositInformation::class)),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->form($this->schemeForm(null, true))
                    ->modalWidth('7xl')
                    ->createAnother(false)
                    ->action(function ($data) {
                        if ($this->giataCode) {
                            $data['product_id'] = $this->giataCode;
                        }
                        if (! $data['expiration_date']) {
                            $data['expiration_date'] = Carbon::create(2112, 02, 02);
                        }
                        /* @var AddDepositInformation $addDepositInformation */
                        $addDepositInformation = app(AddDepositInformation::class);
                        $addDepositInformation->createWithConditions($data);
                    })
                    ->tooltip('Add New Deposit Information')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(fn () => Gate::allows('create', DepositInformation::class)),
            ]);
    }

    public function render()
    {
        return view('livewire.deposit-information-table');

    }
}
