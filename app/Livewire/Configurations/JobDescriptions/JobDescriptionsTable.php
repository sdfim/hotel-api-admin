<?php

namespace App\Livewire\Configurations\JobDescriptions;

use App\Models\Configurations\ConfigJobDescription;
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

class JobDescriptionsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigJobDescription::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description')
                    ->wrap(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (ConfigJobDescription $record): string => route('configurations.job-descriptions.edit', $record))
                        ->visible(fn (ConfigJobDescription $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (ConfigJobDescription $record) => $record->delete())
                        ->visible(fn (ConfigJobDescription $record) => Gate::allows('delete', $record)),
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
                    ->url(fn (): string => route('configurations.job-descriptions.create'))
                    ->visible(fn () => Gate::allows('create', ConfigJobDescription::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.job-descriptions.job-descriptions-table');
    }
}
