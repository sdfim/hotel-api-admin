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
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Modules\Enums\VendorTypeEnum;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsuranceRateTier;
use Modules\Insurance\Models\InsuranceType;

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
            Grid::make(2)
                ->schema([
                    Select::make('vendor_id')
                        ->label('Vendor')
                        ->options(fn () => Vendor::where('type', 'like', '%'.VendorTypeEnum::INSURANCE->value.'%')->pluck('name', 'id')->toArray())
                        ->preload()
                        ->required(),
                    Select::make('insurance_type_id')
                        ->label('Insurance Type')
                        ->options(fn () => InsuranceType::pluck('name', 'id')->toArray())
                        ->preload()
                        ->required(),
                    TextInput::make('min_trip_cost')
                        ->label('Min Trip Cost (PP)')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required()
                        ->minValue(0),
                    TextInput::make('max_trip_cost')
                        ->label('Max Trip Cost (PP)')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required()
                        ->minValue(0),
                    TextInput::make('consumer_plan_cost')
                        ->label('Consumer Plan Cost')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required()
                        ->minValue(0),
                    TextInput::make('net_to_trip_mate')
                        ->label('Net to Trip Mate')
                        ->numeric()
                        ->inputMode('decimal')
                        ->required()
                        ->minValue(0),
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
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('insuranceType.name')
                    ->label('Insurance type')
                    ->toggleable()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('min_trip_cost')
                    ->label('Min Trip Cost (PP)')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('max_trip_cost')
                    ->label('Max Trip Cost (PP)')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('consumer_plan_cost')
                    ->label('Consumer Plan Cost')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ujv_retention')
                    ->label('UJV Retention')
                    ->toggleable()
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
                        $this->validateData($data, $record);
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
                        $this->validateData($data);
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
                ActionGroup::make([
                    CreateAction::make('importInsuranceRateTiers')
                        ->label('Import from CSV')
                        ->form([
                            Select::make('vendor_id')
                                ->label('Vendor')
                                ->options(fn () => Vendor::where('type', 'like', '%'.VendorTypeEnum::INSURANCE->value.'%')->pluck('name', 'id')->toArray())
                                ->preload()
                                ->required(),
                            Select::make('insurance_type_id')
                                ->label('Insurance Type')
                                ->options(fn () => InsuranceType::pluck('name', 'id')->toArray())
                                ->preload()
                                ->required(),
                            FileUpload::make('file')
                                ->label('Upload CSV File')
                                ->required()
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
                        ->createAnother(false)
                        ->action(function (array $data) {
                            Artisan::call('import:insurance-rate-tiers', [
                                'vendor_id' => $data['vendor_id'],
                                'insurance_type_id' => $data['insurance_type_id'],
                                'file' => storage_path('app/public/'.$data['file']),
                            ]);

                            $output = Artisan::output();

                            if (! str_contains($output, 'Error')) {
                                Notification::make()
                                    ->title('Insurance rate tiers imported successfully')
                                    ->body($output)
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Failed to import insurance rate tiers')
                                    ->body($output)
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->tooltip('Import Insurance Rate Tiers')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->visible(fn (): bool => Gate::allows('create', InsuranceRateTier::class)),
                    CreateAction::make('exportInsuranceRateTiers')
                        ->label('Export to CSV')
                        ->action(function (array $data) {
                            Artisan::call('export:insurance-rate-tiers', [
                                'vendor_id' => $data['vendor_id'],
                                'insurance_type_id' => $data['insurance_type_id'],
                            ]);

                            $output = Artisan::output();

                            if (! str_contains($output, 'Error')) {
                                $vendor_name = Vendor::where('id', $data['vendor_id'])->value('name');
                                $insurance_type_name = InsuranceType::where('id', $data['insurance_type_id'])->value('name');
                                $fileName = "insurance_rate_tiers_{$vendor_name}_{$insurance_type_name}.csv";
                                $filePath = storage_path("app/public/exports/{$fileName}");

                                if (file_exists($filePath)) {
                                    return response()->download($filePath)->deleteFileAfterSend(true);
                                } else {
                                    Notification::make()
                                        ->title('Failed to export insurance rate tiers')
                                        ->body('File not found.')
                                        ->danger()
                                        ->send();
                                }
                            } else {
                                Notification::make()
                                    ->title('Failed to export insurance rate tiers')
                                    ->body($output)
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->form([
                            Select::make('vendor_id')
                                ->label('Vendor')
                                ->options(fn () => Vendor::where('type', 'like', '%'.VendorTypeEnum::INSURANCE->value.'%')->pluck('name', 'id')->toArray())
                                ->preload()
                                ->required(),
                            Select::make('insurance_type_id')
                                ->label('Insurance Type')
                                ->options(fn () => InsuranceType::pluck('name', 'id')->toArray())
                                ->preload()
                                ->required(),
                        ])
                        ->modalHeading('Export Insurance Rate Tiers')
                        ->createAnother(false)
                        ->tooltip('Export Insurance Rate Tiers')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->visible(fn (): bool => Gate::allows('create', InsuranceRateTier::class)),
                ])
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->tooltip('Import/Export Rate Tiers')
                    ->iconButton(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->filters([
                SelectFilter::make('insurance_type_id')
                    ->label('Insurance Type')
                    ->options(InsuranceType::pluck('name', 'id')->toArray()),
                SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->options(Vendor::where('type', 'like', '%'.VendorTypeEnum::INSURANCE->value.'%')->pluck('name', 'id')->toArray()),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.rate-tiers.rate-tiers-table');
    }

    protected function validateData(array $data, ?InsuranceRateTier $record = null)
    {
        $minTripCost = $data['min_trip_cost'];
        $maxTripCost = $data['max_trip_cost'];
        $vendorId = $data['vendor_id'];
        $insuranceTypeId = $data['insurance_type_id'];

        if ($maxTripCost !== null && $minTripCost > $maxTripCost) {
            Notification::make()
                ->title('Validation Error')
                ->body('Min Trip Cost cannot be greater than Max Trip Cost.')
                ->danger()
                ->send();
            throw ValidationException::withMessages([
                'min_trip_cost' => 'Min Trip Cost cannot be greater than Max Trip Cost.',
            ]);
        }

        $exists = InsuranceRateTier::where('vendor_id', $vendorId)
            ->where('insurance_type_id', $insuranceTypeId)
            ->where('min_trip_cost', '<=', $minTripCost)
            ->where('max_trip_cost', '>=', $minTripCost)
            ->where('id', '!=', $record->id ?? null)
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Validation Error')
                ->body('An interval with this min trip cost already exists for the selected vendor and insurance type.')
                ->danger()
                ->send();
            throw ValidationException::withMessages([
                'min_trip_cost' => 'An interval with this min trip cost already exists for the selected vendor and insurance type.',
            ]);
        }

        $exists = InsuranceRateTier::where('vendor_id', $vendorId)
            ->where('insurance_type_id', $insuranceTypeId)
            ->where('min_trip_cost', '<=', $maxTripCost)
            ->where('max_trip_cost', '>=', $maxTripCost)
            ->where('id', '!=', $record->id ?? null)
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Validation Error')
                ->body('An interval with this max trip cost already exists for the selected vendor and insurance type.')
                ->danger()
                ->send();
            throw ValidationException::withMessages([
                'max_trip_cost' => 'An interval with this max trip cost already exists for the selected vendor and insurance type.',
            ]);
        }

        $consumerPlanCost = $data['consumer_plan_cost'];
        $netToTripMate = $data['net_to_trip_mate'];

        if ($consumerPlanCost < $netToTripMate) {
            Notification::make()
                ->title('Validation Error')
                ->body('Consumer Plan Cost cannot be less than Net to Trip Mate.')
                ->danger()
                ->send();
            throw ValidationException::withMessages([
                'consumer_plan_cost' => 'Consumer Plan Cost cannot be less than Net to Trip Mate.',
            ]);
        }
    }
}
