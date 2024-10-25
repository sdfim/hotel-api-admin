<?php

namespace App\Livewire\Configurations\Attributes;

use App\Models\Configurations\ConfigAttribute;
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

class AttributesTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ConfigAttribute::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('default_value'),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn (ConfigAttribute $record): string => route('configurations.attributes.edit', $record))
                        ->visible(fn (ConfigAttribute $record) => Gate::allows('update', $record)),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->action(fn (ConfigAttribute $record) => $record->delete())
                        ->visible(fn (ConfigAttribute $record) => Gate::allows('delete', $record)),
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
                    ->url(fn (): string => route('configurations.attributes.create'))
                    ->visible(fn () => Gate::allows('create', ConfigAttribute::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.attributes.attributes-table');
    }
}
