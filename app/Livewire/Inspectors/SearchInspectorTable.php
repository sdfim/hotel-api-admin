<?php

namespace App\Livewire\Inspectors;

use App\Models\ApiSearchInspector;
use App\Models\Suppliers;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Actions\ViewAction;
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
            ->query(ApiSearchInspector::orderBy('created_at','DESC'))
            ->columns([
                TextColumn::make('id')
                    ->searchable(),
				TextColumn::make('search_id')
                    ->searchable()
					->label('Search ID'),
                TextColumn::make('type')
                    ->searchable()
					->label('Endpoint'),
                TextColumn::make('token.id')
                    ->numeric()
                    ->searchable(),
                TextColumn::make('suppliers')
                ->formatStateUsing(function (ApiSearchInspector $record): string{
                    $suppliers_name_string = '';
                    $suppliers_array = explode(',',$record->suppliers);
                    for($i = 0; $i < count($suppliers_array); $i++){
                        $supplier = Suppliers::find($suppliers_array[$i]);
                        if($i == (count($suppliers_array)-1)){
                            $suppliers_name_string .= $supplier->name;
                        }else{
                            $suppliers_name_string .= $supplier->name . ', ';
                        }
                    }
                    return $suppliers_name_string;
                }),
//                    ->searchable(),

                ViewColumn::make('request')->view('dashboard.search-inspector.column.request'),
                TextColumn::make('created_at')
                    ->dateTime()
					->sortable()
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
                ViewAction::make()
                        ->url(fn(ApiSearchInspector $record): string => route('search-inspector.show', $record))
                        ->label('View response')
                        ->color('info'),
                
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
