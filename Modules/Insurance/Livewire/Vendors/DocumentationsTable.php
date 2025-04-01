<?php

namespace Modules\Insurance\Livewire\Vendors;

use App\Helpers\ClassHelper;
use App\Livewire\Configurations\InsuranceDocumentationTypes\InsuranceDocumentationTypesForm;
use App\Models\Configurations\ConfigInsuranceDocumentationType;
use App\Models\Enums\RoleSlug;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Modules\Enums\InsuranceDocTypeEnum;
use Modules\Enums\VendorTypeEnum;
use Modules\HotelContentRepository\Livewire\Components\CustomToggle;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsuranceProviderDocumentation;

class DocumentationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function mount(InsuranceProviderDocumentation $record): void
    {
        $data = $record->toArray();
        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function getVisibilityOptions(?string $documentType): array
    {
        if (! $documentType) {
            return [];
        }

        $arr = ConfigInsuranceDocumentationType::where('id', $documentType)->first();
        if (! $arr) {
            return [];
        }

        return $arr->viewable;
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
                        ->rules(['required']),

                    Select::make('document_type_id')
                        ->label('Type Document')
                        ->reactive()
                        ->createOptionForm(app(InsuranceDocumentationTypesForm::class)->getSchema())
                        ->createOptionUsing(function (array $data) {
                            ConfigInsuranceDocumentationType::create($data);
                            Notification::make()
                                ->title('Document type created successfully')
                                ->success()
                                ->send();
                        })
                        ->relationship('documentType', 'name_type')
                        ->native(false)
                        ->rules(['required']),

                ]),
            Grid::make(6)
                ->schema([
                    CustomToggle::make('viewable.External')
                        ->label('External')
                        ->visible(fn (callable $get) => in_array('External', array_values($this->getVisibilityOptions($get('document_type_id'))))),
                    CustomToggle::make('viewable.Internal')
                        ->label('Internal')
                        ->visible(fn (callable $get) => in_array('Internal', array_values($this->getVisibilityOptions($get('document_type_id'))))),
                ]),
            FileUpload::make('path')
                ->label('Upload file')
                ->directory('insurance-documentation')
                ->visibility('private')
                ->downloadable()
                ->rules(['required']),
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
                TextColumn::make('documentType.name_type')
                    ->label('Document type')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => InsuranceDocTypeEnum::tryFrom($state)?->label() ?? $state),
            ])
            ->actions([
//                Action::make('download')
//                    ->icon('heroicon-s-arrow-down-circle')
//                    ->iconButton()
//                    ->color('success')
//                    ->tooltip('Download File')
//                    ->action(function (InsuranceProviderDocumentation $record) {
//                        $filePath = $record->path;
//
//                        if (Storage::exists($filePath)) {
//                            redirect(Storage::url($filePath));
//                        }
//
//                        Notification::make()
//                            ->title('File not found')
//                            ->danger()
//                            ->send();
//
//                        return false;
//                    }),
//                Action::make('View')
//                    ->icon('heroicon-s-eye')
//                    ->iconButton()
//                    ->color('success')
//                    ->tooltip('View File')
//                    ->url(function (InsuranceProviderDocumentation $record) {
//                        $filePath = $record->path;
//
//                        if (Storage::exists($filePath)) {
//                            return Storage::url($filePath);
//                        }
//
//                        return false;
//                    })
//                    ->openUrlInNewTab(),
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Provider Documentation')
                    ->form(fn (InsuranceProviderDocumentation $record) => $this->schemeForm($record))
                    ->closeModalByClickingAway(false)
                    ->fillForm(function (InsuranceProviderDocumentation $record) {
                        return $record->toArray();
                    })
                    ->visible(fn (InsuranceProviderDocumentation $record): bool => Gate::allows('update', $record))
                    ->action(function (InsuranceProviderDocumentation $record, array $data, EditAction $action) {
                        if (! $data['path'] || $data['path'] instanceof UploadedFile) {
                            Notification::make()
                                ->title('File not found')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
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

                        if (Storage::exists($filePath)) {
                            Storage::delete($filePath);
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
                    ->closeModalByClickingAway(false)
                    ->action(function (array $data, CreateAction $action) {
                        if (! $data['path'] || $data['path'] instanceof UploadedFile) {
                            Notification::make()
                                ->title('File not found')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                        InsuranceProviderDocumentation::create($data);

                        if (Arr::get($action->getArguments(), 'another', false)) {
                            Notification::make()
                                ->title('Documentation created successfully')
                                ->success()
                                ->send();

                            $this->reset('mountedTableActionsData');
                            $this->mountedTableActionsData[0]['viewable'] = ['Internal' => false, 'External' => false];
                            $this->mountedTableActionsData[0]['path'] = [];
                            $action->halt();
                        }

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
