<?php

namespace App\Livewire;

use App\Models\ApiExceptionReport;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;


class ApiExceptionReportsTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table (Table $table): Table
    {
        return $table
            ->query(ApiExceptionReport::orderBy('created_at','DESC'))
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('type')
                ->sortable(),
                TextColumn::make('supplier.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('request')
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('response_path')->view('dashboard.content-loader-exceptions.column.request'),

            ])
            ->filters([
                //
                SelectFilter::make('type')
                ->options([
                    'Debug' => 'Debug',
                    'Error' => 'Error',
                ])
            ])
            ->actions([
                // ViewAction::make()
                //         ->url(fn(ApiExceptionReport $record): string => route('content-loader-exceptions.show', $record))
                //         ->label('View response')
                //         ->color('info'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render (): View
    {
        return view('livewire.api-exception-reports-table');
    }
}