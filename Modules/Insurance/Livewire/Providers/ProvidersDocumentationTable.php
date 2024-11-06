<?php

namespace Modules\Insurance\Livewire\Providers;

use App\Helpers\ClassHelper;
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
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Modules\Insurance\Models\InsuranceProviderDocumentation;

class ProvidersDocumentationTable extends Component implements HasForms, HasTable
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
                    Select::make('provider_id')
                        ->label('Provider Documentation')
                        ->relationship(name: 'provider', titleAttribute: 'name')
                        ->preload()
                        ->required(),
                    Select::make('document_type')
                        ->label('Type Document')
                        ->options([
                            'privacy_policy' => 'Privacy Policy',
                            'terms_and_condition' => 'Terms & Conditions',
                        ])
                        ->unique(ignorable: $record)
                        ->required(),
                ]),
            FileUpload::make('uri')
                ->label('File document')
                ->disk('public')
                ->directory('insurance-documentation')
                ->visibility('private')
                ->downloadable()
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(InsuranceProviderDocumentation::query())
            ->columns([
                TextColumn::make('provider.name')
                    ->label('Provider name')
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
                    ->action(function (InsuranceProviderDocumentation $record) {
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
                    ->tooltip('Add New Provider Documentation')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.providers.providers-documentation-table');
    }
}
