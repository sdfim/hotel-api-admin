<?php

namespace App\Livewire\Configurations\Attributes;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigAttribute;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->searchable()
                    ->formatStateUsing(function ($state, $record) {
                        return $record->categories->pluck('name')->map(fn($name) => \Illuminate\Support\Str::of($name)->replace('_', ' ')->title())->join(', ');
                    }),
            ])
            ->actions([
                //                ActionGroup::make([
                EditAction::make()
                    ->iconButton()
                    ->url(fn (ConfigAttribute $record): string => route('configurations.attributes.edit', $record))
                    ->visible(fn (ConfigAttribute $record) => Gate::allows('update', $record)),
                //                    DeleteAction::make()
                //                        ->requiresConfirmation()
                //                        ->action(fn (ConfigAttribute $record) => $record->delete())
                //                        ->visible(fn (ConfigAttribute $record) => Gate::allows('delete', $record)),
                //                ]),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action(fn ($records) => ConfigAttribute::destroy($records->pluck('id')->toArray()))
                    ->requiresConfirmation()
                    ->visible(fn () => Gate::allows('delete', ConfigAttribute::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.attributes.create'))
                    ->visible(fn () => Gate::allows('create', ConfigAttribute::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.attributes.attributes-table');
    }
}
