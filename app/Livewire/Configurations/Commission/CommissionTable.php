<?php

namespace App\Livewire\Configurations\Commission;

use App\Helpers\ClassHelper;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Commission;

class CommissionTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(Commission::query())
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->url(fn (Commission $record): string => route('configurations.commissions.edit', $record))
                    ->visible(fn (Commission $record) => Gate::allows('update', $record)),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->action(fn ($records) => Commission::destroy($records->pluck('id')->toArray()))
                    ->requiresConfirmation()
                    ->visible(fn () => Gate::allows('delete', Commission::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->icon('heroicon-o-plus')
                    ->iconButton()
                    ->url(fn (): string => route('configurations.commissions.create'))
                    ->visible(fn () => Gate::allows('create', Commission::class)),
            ]);
    }

    public function render(): View
    {
        return view('livewire.configurations.commissions.commission-table');
    }
}
