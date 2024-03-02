<?php

namespace App\Livewire;

use App\Models\ExpediaContent;
use Exception;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Livewire\Component;

class ExpediaTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @param Table $table
     * @return Table
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10])
            ->query(ExpediaContent::query()->orderBy('rating', 'desc'))
            ->columns([
                TextColumn::make('property_id')
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where('property_id', $search);
                    }, isIndividual: true)
                    ->toggleable(),
                ViewColumn::make('name')
                    ->toggleable()
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->view('dashboard.expedia.column.name-field'),
                TextColumn::make('rating')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('city')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->numeric()
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->toggleable(),
                ViewColumn::make('address')
                    ->view('dashboard.expedia.column.address-field')
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                ViewColumn::make('mapperGiataExpedia.giata_id')
                    ->label('Giata id')
                    ->view('dashboard.expedia.column.giata_id')
                    ->searchable(isIndividual: true),
                TextColumn::make('mapperGiataExpedia.step')
                    ->searchable(isIndividual: true)
                    ->label(new HtmlString('Strategy<br> Mapper'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '1', '2', '10' => 'success',
                        '3', '4', '5', '6', '7' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('is_active')
                    ->searchable(isIndividual: true)
                    ->label('Active')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '1' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),
                ViewColumn::make('edit')
                    ->view('dashboard.expedia.column.add-giata')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('is_empty')
                    ->form([
                        Checkbox::make('is_empty')
                            ->label('Without Giata ID')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['is_empty']) {
                            return $query->with('mapperGiataExpedia')->whereDoesntHave('mapperGiataExpedia', function (Builder $query) {
                                $query->whereNotNull('giata_id');
                            });
                        } else return $query;
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['is_empty']) {
                            return null;
                        }
                        return 'Without Giata ID';
                    })
            ]);
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.expedia-table');
    }
}
