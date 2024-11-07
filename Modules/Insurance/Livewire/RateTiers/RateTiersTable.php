<?php

namespace Modules\Insurance\Livewire\RateTiers;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\Insurance\Models\InsuranceRateTier;

class RateTiersTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(?InsuranceRateTier $record = null): array
    {
        return [
            Grid::make(3)
                ->schema([
                    Select::make('insurance_provider_id')
                        ->label('Provider')
                        ->relationship(name: 'provider', titleAttribute: 'name')
                        ->preload()
                        ->required(),
                    TextInput::make('min_price')
                        ->label('Min Price')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required()
                        ->unique(ignorable: $record),
                    TextInput::make('max_price')
                        ->label('Max Price')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required()
                        ->unique(ignorable: $record),
                ]),
            Grid::make()
                ->schema([
                    Select::make('rate_type')
                        ->options([
                            'fixed' => 'Fixed price',
                            'percentage' => 'Percentage',
                        ])
                        ->live()
                        ->required(),
                    TextInput::make('rate_value')
                        ->numeric()
                        ->required()
                        ->suffixIcon(function (Get $get) {
                            return match ($get('rate_type')) {
                                null, '' => false,
                                'fixed' => 'heroicon-o-banknotes',
                                'percentage' => 'heroicon-o-receipt-percent',
                            };
                        }),
                ])
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(InsuranceRateTier::query())
            ->columns([
                TextColumn::make('provider.name')
                    ->label('Insurance Provider')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('min_price')
                    ->label('Min Price')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('max_price')
                    ->label('Max Price')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('rate_type')
                    ->label('Rate Type')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'fixed' => 'Fixed price',
                        'percentage' => 'Percentage',
                    }),
                TextColumn::make('rate_value')
                    ->label('Rate Value')
                    ->sortable()
                    ->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Rate Tier')
                    ->form(fn(InsuranceRateTier $record) => $this->schemeForm($record))
                    ->fillForm(function (InsuranceRateTier $record) {
                        return $record->toArray();
                    })
                    ->action(function (InsuranceRateTier $record, array $data) {
                        $record->update($data);

                        Notification::make()
                            ->title('Updated successfully')
                            ->success()
                            ->send();

                        return $data;
                    }),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete Rate Tier')
                    ->requiresConfirmation()
                    ->action(function (InsuranceRateTier $record) {
                        $record->delete();

                        Notification::make()
                            ->title('Deleted successfully')
                            ->success()
                            ->send();
                    })
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->action(function (array $data) {
                        InsuranceRateTier::create($data);

                        Notification::make()
                            ->title('Created successfully')
                            ->success()
                            ->send();

                        return $data;
                    })
                    ->tooltip('Add New Rate Tier')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.rate-tiers.rate-tiers-table');
    }
}
