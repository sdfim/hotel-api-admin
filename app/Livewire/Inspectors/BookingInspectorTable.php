<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiBookingInspector;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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

                ViewColumn::make('response_path')
					->view('dashboard.booking-inspector.column.response')
					->label('Response'),

				ViewColumn::make('client_response_path')
					->view('dashboard.booking-inspector.column.client-response')
					->label(new HtmlString('Clear <br />  Response')),

                TextColumn::make('created_at')
                    ->dateTime()
					->sortable()
            ])
            ->filters([

            ])
            ->actions([
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
