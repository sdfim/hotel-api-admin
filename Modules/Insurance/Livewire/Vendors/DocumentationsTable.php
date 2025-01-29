<?php

namespace Modules\Insurance\Livewire\Vendors;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Modules\Enums\InsuranceDocTypeEnum;
use Modules\Enums\InsuranceDocVisibilityEnum;
use Modules\Enums\VendorTypeEnum;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsuranceProviderDocumentation;

class DocumentationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function getVisibilityOptions(?string $documentType): array
    {
        if (! $documentType) {
            return [];
        }

        $options = [
            InsuranceDocTypeEnum::PRIVACY_POLICY->value => [InsuranceDocVisibilityEnum::EXTERNAL->value],
            InsuranceDocTypeEnum::TERMS_AND_CONDITION->value => [InsuranceDocVisibilityEnum::EXTERNAL->value],
            InsuranceDocTypeEnum::TRAVEL_PROTECTION_PLAN_SUMMARY->value => [InsuranceDocVisibilityEnum::EXTERNAL->value],
            InsuranceDocTypeEnum::SCHEDULE_OF_BENEFITS_PLAN_COSTS->value => [InsuranceDocVisibilityEnum::INTERNAL->value],
            InsuranceDocTypeEnum::CLAIM_PROCESS->value => [InsuranceDocVisibilityEnum::INTERNAL->value],
            InsuranceDocTypeEnum::EMERGENCY_TRAVEL_ASSISTANCE_PLATINUM->value => [InsuranceDocVisibilityEnum::EXTERNAL->value],
            InsuranceDocTypeEnum::EMERGENCY_TRAVEL_ASSISTANCE_SILVER->value => [InsuranceDocVisibilityEnum::EXTERNAL->value],
            InsuranceDocTypeEnum::TRIPMATE_CLAIMS->value => [InsuranceDocVisibilityEnum::INTERNAL->value],
            InsuranceDocTypeEnum::GENERAL_INFORMATION->value => [InsuranceDocVisibilityEnum::INTERNAL->value, InsuranceDocVisibilityEnum::EXTERNAL->value],
        ];

        return array_intersect_key(InsuranceDocVisibilityEnum::getOptions(), array_flip($options[$documentType] ?? []));
    }

    public function schemeForm(?InsuranceProviderDocumentation $record = null): array
    {
        return [
            Grid::make()
                ->schema([
                    Select::make('vendor_id')
                        ->label('Provider Documentation')
                        ->options(fn () => Vendor::where('type', 'like', '%'.VendorTypeEnum::INSURANCE->value.'%')->pluck('name', 'id')->toArray())
                        ->preload()
                        ->required(),

                    Select::make('document_type')
                        ->label('Type Document')
                        ->reactive()
                        ->options(InsuranceDocTypeEnum::getOptions())
                        ->required(),

                    Select::make('viewable')
                        ->label('Viewable')
                        ->options(fn (callable $get) => $this->getVisibilityOptions($get('document_type')))
                        ->required(),
                ]),
            FileUpload::make('path')
                ->label('Upload file')
                ->disk('public')
                ->directory('insurance-documentation')
                ->visibility('private')
                ->downloadable(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InsuranceProviderDocumentation::query()
                    ->when(
                        auth()->user()->currentTeam && ! auth()->user()->hasRole(RoleSlug::ADMIN->value),
                        fn ($q) => $q->where('vendor_id', auth()->user()->currentTeam->vendor_id),
                    )
            )
            ->columns([
                TextColumn::make('vendor.name')
                    ->label('Vendor name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('document_type')
                    ->label('Document type')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => InsuranceDocTypeEnum::tryFrom($state)?->label() ?? $state),
            ])
            ->actions([
                Action::make('download')
                    ->icon('heroicon-s-arrow-down-circle')
                    ->color('success')
                    ->label('Download File')
                    ->action(function (InsuranceProviderDocumentation $record) {
                        $filePath = $record->path;

                        if (Storage::disk('public')->exists($filePath)) {
                            return response()->download(
                                Storage::disk('public')->path($filePath),
                                basename($filePath)
                            );
                        }

                        Notification::make()
                            ->title('File not found')
                            ->danger()
                            ->send();

                        return false;
                    }),
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Provider Documentation')
                    ->form(fn (InsuranceProviderDocumentation $record) => $this->schemeForm($record))
                    ->fillForm(function (InsuranceProviderDocumentation $record) {
                        return $record->toArray();
                    })
                    ->visible(fn (InsuranceProviderDocumentation $record): bool => Gate::allows('update', $record))
                    ->action(function (InsuranceProviderDocumentation $record, array $data) {
                        $record->update($data);

                        Notification::make()
                            ->title('Updated successfully')
                            ->success()
                            ->send();

                        return $data;
                    }),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete Provider Documentation')
                    ->requiresConfirmation()
                    ->visible(fn (InsuranceProviderDocumentation $record): bool => Gate::allows('delete', $record))
                    ->action(function (InsuranceProviderDocumentation $record) {
                        $filePath = $record->path;

                        if (Storage::disk('public')->exists($filePath)) {
                            Storage::disk('public')->delete($filePath);
                        } else {
                            Notification::make()
                                ->title('Can\'t delete file')
                                ->danger()
                                ->send();
                        }

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
                        InsuranceProviderDocumentation::create($data);

                        Notification::make()
                            ->title('Created successfully')
                            ->success()
                            ->send();

                        return $data;
                    })
                    ->tooltip('Add Provider Documentation')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(fn (): bool => Gate::allows('create', InsuranceProviderDocumentation::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.vendors.documentations-table');
    }
}
