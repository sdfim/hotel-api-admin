<?php

namespace Modules\HotelContentRepository\Livewire\Vendor;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Enums\VendorTypeEnum;
use Modules\HotelContentRepository\Models\Vendor;

class VendorTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->query(Vendor::query())
            ->modifyQueryUsing(function (Builder $query): Builder {
                if (! auth()->user()->hasRole(RoleSlug::ADMIN->value)) {
                    $vendorIds = auth()->user()->allTeams()->pluck('vendor_id')->toArray();

                    return $query->whereIn('id', $vendorIds);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%']),
                IconColumn::make('independent_flag')
                    ->label('Independent')
                    ->sortable()
                    ->toggleable()
                    ->boolean(),
                IconColumn::make('verified')
                    ->label('Activated')
                    ->sortable()
                    ->toggleable()
                    ->boolean(),
                TextColumn::make('address')
                    ->label('Address')
                    ->wrap()
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%']),
                TextColumn::make('products_count')
                    ->label('Products')
                    ->getStateUsing(function ($record) {
                        return $record->products()->count();
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->withCount('products')
                            ->orderBy('products_count', $direction);
                    }),
                TextInputColumn::make('website')
                    ->label('Website')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%']),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Vendor')
                    ->url(fn (Vendor $record): string => route('vendor-repository.edit', $record))
                    ->visible(fn (Vendor $record) => Gate::allows('update', $record)),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete Vendor')
                    ->requiresConfirmation()
                    ->action(function (Vendor $record) {
                        \DB::transaction(function () use ($record) {
                            foreach ($record->products as $product) {
                                if ($product->related) {
                                    $product->related->delete();
                                }
                                $product->delete();
                            }
                            $record->delete();
                        });
                    })
                    ->visible(fn (Vendor $record) => Gate::allows('delete', $record)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->url(fn (): string => route('vendor-repository.create'))
                    ->tooltip('Add New Vendor')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->visible(fn () => Gate::allows('create', Vendor::class))
                    ->iconButton(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->multiple()
                    ->options(VendorTypeEnum::getOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['values'])) {
                            foreach ($data['values'] as $value) {
                                $query->orWhereJsonContains('type', $value);
                            }
                        }

                        return $query;
                    }),
                SelectFilter::make('independent_flag')
                    ->label('Independent')
                    ->options([
                        1 => 'Yes',
                        0 => 'No',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value'])) {
                            return $query->where('independent_flag', $data['value']);
                        }

                        return $query;
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.vendors.vendor-table');
    }
}
