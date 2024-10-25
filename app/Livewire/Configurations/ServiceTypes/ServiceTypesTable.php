<?php

namespace App\Livewire\Configurations\ServiceTypes;

use App\Models\Configurations\ConfigServiceType;
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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Livewire\Component;

class ServiceTypesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigServiceType::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description')
                    ->wrap(),
                TextColumn::make('cost'),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (ConfigServiceType $record): string => route('configurations.service-types.edit', $record))
                        ->visible(fn (ConfigServiceType $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (ConfigServiceType $record) => $record->delete())
                        ->visible(fn (ConfigServiceType $record) => Gate::allows('delete', $record)),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => 'btn text-violet-500 hover:text-white border-violet-500
                    hover:bg-violet-600 hover:border-violet-600 focus:bg-violet-600 focus:text-white
                    focus:border-violet-600 focus:ring focus:ring-violet-500/30
                    active:bg-violet-600 active:border-violet-600'])
                    ->iconButton()
                    ->icon(new HtmlString('<i class="bx bx-plus block text-lg"></i>'))
                    ->url(fn (): string => route('configurations.service-types.create'))
                    ->visible(fn () => Gate::allows('create', ConfigServiceType::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.service-types.service-types-table');
    }
}
