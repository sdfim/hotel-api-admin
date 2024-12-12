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
use Modules\Insurance\Models\InsuranceProviderDocumentation;

class DocumentationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public array $documentTypes = [];

    public function mount(): void
    {
        $this->documentTypes = [
            'privacy_policy' => 'Privacy Policy',
            'terms_and_condition' => 'Terms & Conditions'
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(?InsuranceProviderDocumentation $record = null): array
    {
        return [
            Grid::make()
                ->schema([
                    Select::make('vendor_id')
                        ->label('Provider Documentation')
                        ->relationship(name: 'vendor', titleAttribute: 'name')
                        ->preload()
                        ->required(),
                    Select::make('document_type')
                        ->label('Type Document')
                        ->options([
                            'privacy_policy' => 'Privacy Policy',
                            'terms_and_condition' => 'Terms & Conditions',
                        ])
                        ->required(),
                ]),
            FileUpload::make('path')
                ->label('Upload file')
                ->disk('public')
                ->directory('insurance-documentation')
                ->visibility('private')
                ->downloadable()
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InsuranceProviderDocumentation::query()
                    ->when(
                        auth()->user()->currentTeam && !auth()->user()->hasRole(RoleSlug::ADMIN->value),
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
                    ->formatStateUsing(fn($state) => $this->documentTypes[$state])
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
                    ->form(fn(InsuranceProviderDocumentation $record) => $this->schemeForm($record))
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
                    })
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
