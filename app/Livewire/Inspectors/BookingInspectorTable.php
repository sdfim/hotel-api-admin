<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiBookingInspector;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\View\View;
use Livewire\Component;
use Illuminate\Support\HtmlString;
use Filament\Tables\Actions\ViewAction;

class BookingInspectorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table (Table $table): Table
    {
        return $table
            ->query(ApiBookingInspector::query())
            ->columns([
                TextColumn::make('id')
                    ->searchable()
					->sortable(),
				TextColumn::make('search_id')
                    ->searchable()
					->sortable(),
				TextColumn::make('booking_id')
                    ->searchable()
					->sortable(),
                TextColumn::make('type')
                    ->searchable(),
				TextColumn::make('sub_type')
                    ->searchable(),
                TextColumn::make('token.id')
                    ->numeric()
                    ->searchable(),
                TextColumn::make('supplier.name')
                    ->numeric()
                    ->searchable(),

                ViewColumn::make('request')->view('dashboard.booking-inspector.column.request'),

                TextColumn::make('created_at')
                    ->dateTime()
					->sortable()
            ])
            ->filters([

            ])
            ->actions([
                ViewAction::make()
                        ->url(fn(ApiBookingInspector $record): string => route('booking-inspector.show', $record))
                        ->label('View response')
                        ->color('info'),
                // ActionGroup::make([
                //     ViewAction::make()
                //         ->url(fn(Channels $record): string => route('channels.show', $record))
                //         ->color('info'),
                //     EditAction::make()
                //         ->url(fn(Channels $record): string => route('channels.edit', $record))
                //         ->color('primary'),
                //     DeleteAction::make()
                //         ->requiresConfirmation()
                //         ->action(fn(Channels $record) => $record->delete())
                //         ->color('danger'),
                // ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render (): View
    {
        return view('livewire.inspectors.booking-inspector-table');
    }
}
