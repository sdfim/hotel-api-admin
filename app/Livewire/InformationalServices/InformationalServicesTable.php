<?php

namespace App\Livewire\InformationalServices;

use App\Helpers\ClassHelper;
use App\Models\InformationalService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Actions\ActionGroup;
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

class InformationalServicesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm($record = null): array
    {
        return [
            Select::make('booking_item')
                ->relationship('bookingItem', 'booking_item')
                ->searchable()
                ->required(),
            Select::make('service_id')
                ->relationship('service', 'name')
                ->searchable()
                ->required(),
            TextInput::make('cost')
                ->numeric('decimal')
                ->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(InformationalService::query())
            ->columns([
                TextColumn::make('booking_item')
                    ->searchable(isIndividual: true),
                TextColumn::make('service.name')
                    ->label('Service Name')
                    ->searchable(isIndividual: true),
                TextColumn::make('cost')->searchable(),
            ]);
        //            ->actions([
        //                ActionGroup::make([
        //                    EditAction::make()
        //                        ->form($this->schemeForm())
        //                        ->fillForm(function ($record) {
        //                            return $record->load('bookingItem', 'service')->toArray();
        //                        })
        //                        ->visible(fn (InformationalService $record) => Gate::allows('update', $record)),
        //                    DeleteAction::make()
        //                        ->requiresConfirmation()
        //                        ->action(fn (InformationalService $record) => $record->delete())
        //                        ->visible(fn (InformationalService $record) => Gate::allows('delete', $record)),
        //                ]),
        //            ])
        //            ->headerActions([
        //                CreateAction::make()
        //                    ->icon('heroicon-o-plus')
        //                    ->iconButton()
        //                    ->form($this->schemeForm())
        //                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
        //                    ->visible(fn () => Gate::allows('create', InformationalService::class)),
        //            ])
    }

    public function render(): View
    {
        return view('livewire.informational-services.informational-services-table');
    }
}
