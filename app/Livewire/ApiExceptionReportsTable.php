<?php

namespace App\Livewire;

use App\Models\ApiExceptionReport;
use Exception;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;

class ApiExceptionReportsTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    /**
     * @param Table $table
     * @return Table
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(ApiExceptionReport::orderBy('created_at', 'DESC'))
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('report_id')
                    ->sortable(),
                TextColumn::make('level')
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'error' => 'danger',
                        'warning' => 'warning',
                        'success' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('supplier.name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('action')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->sortable()
                    ->searchable(),
                ViewColumn::make('response_path')
                    ->view('dashboard.exceptions-report.column.request')
                    ->label('Response'),
                TextColumn::make('created_at')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->options([
                        'Debug' => 'Debug',
                        'Error' => 'Error',
                        'Warning' => 'Warning',
                        'success' => 'Success',
                    ])
            ]);
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.api-exception-reports-table');
    }
}
