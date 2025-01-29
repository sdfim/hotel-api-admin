<?php

namespace Modules\Insurance\Livewire\RateTiers;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Enums\VendorTypeEnum;
use Modules\HotelContentRepository\Models\Vendor;
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
                    Select::make('vendor_id')
                        ->label('Vendor')
                        ->options(fn () => Vendor::where('type', 'like', '%'.VendorTypeEnum::INSURANCE->value.'%')->pluck('name', 'id')->toArray())
                        ->preload()
                        ->required(),
                    TextInput::make('min_trip_cost')
                        ->label('Min Trip Cost')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required()
                        ->unique(ignorable: $record),
                    TextInput::make('max_trip_cost')
                        ->label('Max Trip Cost')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required()
                        ->unique(ignorable: $record),
                    TextInput::make('consumer_plan_cost')
                        ->label('Consumer Plan Cost')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required(),
                    TextInput::make('ujv_retention')
                        ->label('UJV Retention')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required(),
                    TextInput::make('net_to_trip_mate')
                        ->label('Net to Trip Mate')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required(),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InsuranceRateTier::query()
                    ->when(
                        auth()->user()->currentTeam && ! auth()->user()->hasRole(RoleSlug::ADMIN->value),
                        fn ($q) => $q->where('vendor_id', auth()->user()->currentTeam->vendor_id),
                    )
            )
            ->columns([
                TextColumn::make('vendor.name')
                    ->label('Insurance Vendor')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('min_trip_cost')
                    ->label('Min Trip Cost')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('max_trip_cost')
                    ->label('Max Trip Cost')
                    ->sortable()
                    ->searchable(isIndividual: true),
                TextColumn::make('consumer_plan_cost')
                    ->label('Consumer Plan Cost')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ujv_retention')
                    ->label('UJV Retention')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('net_to_trip_mate')
                    ->label('Net to Trip Mate')
                    ->sortable()
                    ->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Rate Tier')
                    ->form(fn (InsuranceRateTier $record) => $this->schemeForm($record))
                    ->fillForm(function (InsuranceRateTier $record) {
                        return $record->toArray();
                    })
                    ->visible(fn (InsuranceRateTier $record): bool => Gate::allows('update', $record))
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
                    ->visible(fn (InsuranceRateTier $record): bool => Gate::allows('delete', $record))
                    ->action(function (InsuranceRateTier $record) {
                        $record->delete();

                        Notification::make()
                            ->title('Deleted successfully')
                            ->success()
                            ->send();
                    }),
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
                    ->iconButton()
                    ->visible(fn (): bool => Gate::allows('create', InsuranceRateTier::class)),
                CreateAction::make('importInsuranceRateTiers')
                    ->label('Import Insurance Rate Tiers')
                    ->form([
                        Select::make('vendor_id')
                            ->label('Vendor')
                            ->options(fn () => Vendor::where('type', 'like', '%'.VendorTypeEnum::INSURANCE->value.'%')->pluck('name', 'id')->toArray())
                            ->preload()
                            ->required(),
                        FileUpload::make('file')
                            ->label('Upload CSV File')
                            ->disk('public')
                            ->acceptedFileTypes(['text/csv'])
                            ->moveFiles()
                            ->directory('rate-tiers')
                            ->visibility('private')
                            ->downloadable(),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure?')
                    ->modalDescription('All existing records for this provider will be deleted and replaced with new data from the file.')
                    ->modalSubmitActionLabel('Yes, proceed')
                    ->modalCancelActionLabel('Cancel')
                    ->disableCreateAnother()
                    ->action(function (array $data) {
                        Artisan::call('import:insurance-rate-tiers', [
                            'vendor_id' => $data['vendor_id'],
                            'file' => storage_path('app/public/'.$data['file']),
                        ]);
                        Notification::make()
                            ->title('Insurance rate tiers imported successfully')
                            ->success()
                            ->send();
                    })
                    ->tooltip('Import Insurance Rate Tiers')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(fn (): bool => Gate::allows('create', InsuranceRateTier::class)),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.rate-tiers.rate-tiers-table');
    }
}
