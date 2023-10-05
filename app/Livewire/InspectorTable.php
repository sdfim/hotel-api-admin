<?php

namespace App\Livewire;

use App\Models\ApiInspector;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\ViewColumn;

class InspectorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table (Table $table): Table
    {
        return $table
            ->query(ApiInspector::query())
            ->columns([
                TextColumn::make('id')
                    //->sortable()
                    ->searchable(),
                TextColumn::make('type')
                    //->sortable()
                    ->searchable(),
                TextColumn::make('token.id')
                    ->numeric()
                    ->searchable(),
                  //  ->sortable(),
                TextColumn::make('supplier.name')
                    ->numeric()
                    ->searchable(),
                   // ->sortable(),
               
                ViewColumn::make('request')->view('dashboard.inspector.column.request'),

				ViewColumn::make('response_path')->view('dashboard.inspector.column.response'),
                
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
        return view('livewire.inspector-table');
    }
}
