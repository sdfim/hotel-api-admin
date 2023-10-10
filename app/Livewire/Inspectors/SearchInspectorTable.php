<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiSearchInspector;
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


class SearchInspectorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table (Table $table): Table
    {
        return $table
            ->query(ApiSearchInspector::query())
            ->columns([
                TextColumn::make('id')
                    ->searchable()
					->label('Search ID'),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('token.id')
                    ->numeric()
                    ->searchable(),
                TextColumn::make('supplier.name')
                    ->numeric()
                    ->searchable(),

                ViewColumn::make('request')->view('dashboard.search-inspector.column.request'),

                ViewColumn::make('response_path')->view('dashboard.search-inspector.column.response')
					->label('Response'),

				ViewColumn::make('client_response_path')
					->view('dashboard.search-inspector.column.client-response')
					->label(new HtmlString('Clear <br />  Response')),

                TextColumn::make('created_at')
                    ->dateTime()
            ])
            ->filters([
                // Filter::make('name')
                // ->form([
                //     TextInput::make('name')
                // ])
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['name'],
                //             fn (Builder $query, $name): Builder => $query->where('name', 'LIKE', '%'.$name.'%'),
                //         );
                // })->indicateUsing(function (array $data): ?string {
                //     if (! $data['name']) {
                //         return null;
                //     }
                //     return 'Name: ' . $data['name'];
                // }),
                // Filter::make('city')
                // ->form([
                //     TextInput::make('city')
                // ])
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['city'],
                //             fn (Builder $query, $city): Builder => $query->where('city', 'LIKE', '%'.$city.'%'),
                //         );
                // })->indicateUsing(function (array $data): ?string {
                //     if (! $data['city']) {
                //         return null;
                //     }
                //     return 'City: ' . $data['city'];
                // }),
                // Filter::make('address')
                // ->form([
                //     TextInput::make('address')
                // ])
                // ->query(function (Builder $query, array $data): Builder {
                //     return $query
                //         ->when(
                //             $data['address'],
                //             fn (Builder $query, $address): Builder => $query->where('address', 'LIKE', '%'.$address.'%'),
                //         );
                // })->indicateUsing(function (array $data): ?string {
                //     if (! $data['address']) {
                //         return null;
                //     }
                //     return 'Address: ' . $data['address'];
                // })
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
        return view('livewire.inspectors.search-inspector-table');
    }
}
