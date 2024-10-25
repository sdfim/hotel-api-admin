<?php

namespace Modules\Insurance\Livewire\Insurance\Providers;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\TextInput;
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
use Livewire\Component;
use Modules\Insurance\Models\InsuranceProvider;

class ProvidersTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(?InsuranceProvider $record = null): array
    {
        return [
            TextInput::make('name')
                ->unique(ignorable: $record)
                ->required()
                ->maxLength(191),
            TextInput::make('contact_info')
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(InsuranceProvider::query())
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('contact_info')
                    ->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Provider')
                    ->form(fn(InsuranceProvider $record) => $this->schemeForm($record))
                    ->fillForm(function (InsuranceProvider $record) {
                        return $record->toArray();
                    })
                    ->action(function (InsuranceProvider $record, array $data) {
                        $record->update($data);

                        Notification::make()
                            ->title('Updated successfully')
                            ->success()
                            ->send();

                        return $data;
                    }),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete Provider')
                    ->requiresConfirmation()
                    ->action(function (InsuranceProvider $record) {
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
                        InsuranceProvider::create($data);

                        Notification::make()
                            ->title('Created successfully')
                            ->success()
                            ->send();

                        return $data;
                    })
                    ->tooltip('Add New Provider')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.providers.providers-table');
    }
}
