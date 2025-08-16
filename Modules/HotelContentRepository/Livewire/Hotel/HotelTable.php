<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Helpers\ClassHelper;
use App\Models\Configurations\ConfigAttribute;
use App\Models\Enums\RoleSlug;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Enums\SupplierNameEnum;
use Modules\HotelContentRepository\Actions\Hotel\AddHotel;
use Modules\HotelContentRepository\Actions\Hotel\DeleteHotel;
use Modules\HotelContentRepository\Actions\Product\DeleteProduct;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Vendor;

class HotelTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?Vendor $vendor = null;

    public function mount(?Vendor $vendor = null)
    {
        $this->vendor = $vendor;
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(function (Builder $query) {
                $query = Hotel::query()
                    ->when(
                        auth()->user()->currentTeam && ! auth()->user()->hasRole(RoleSlug::ADMIN->value),
                        fn ($q) => $q->whereHas('product', function (Builder $query) {
                            $vendorIds = auth()->user()->allTeams()->pluck('vendor_id')->toArray();
                            $query->whereIn('vendor_id', $vendorIds);
                        })
                    );

                if ($this->vendor?->id) {
                    $query->whereHas('product', function (Builder $query) {
                        $query->where('vendor_id', $this->vendor->id);
                    });
                }

                return $query;
            })
            ->columns([
                ImageColumn::make('product.hero_image_thumbnails')
                    ->size('100px'),

                IconColumn::make('product.verified')
                    ->label('Verified')
                    ->toggleable()
                    ->boolean(),

                IconColumn::make('product.onSale')
                    ->label('onSale')
                    ->toggleable()
                    ->boolean(),

                TextColumn::make('product.name')
                    ->label('Name')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('giata_code')
                    ->label('GIATA Code')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('giataCode.mappings')
                    ->label('Mappings')
                    ->html()
                    ->getStateUsing(function ($record) {
                        return $record->giataCode?->mappings
//                            ->filter(fn ($mapping) => $mapping->supplier !== 'HBSI')
                            ->map(fn ($mapping) => "{$mapping->supplier_id}: {$mapping->supplier}")
                            ->join('<br>');
                    })
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('product.address')
                    ->label('Address')
                    ->searchable()
                    ->wrap()
                    ->getStateUsing(function ($record) {
                        $string = '';
                        foreach ($record->address as $item) {
                            if (is_array($item)) {
                                continue;
                            }
                            $string .= $item.', ';
                        }

                        return rtrim($string, ', ');
                    })
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('star_rating')
                    ->label('Star')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('num_rooms')
                    ->label('Rooms')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('combined_sources')
                    ->label('Combined Sources')
                    ->toggleable()
                    ->sortable()
                    ->default(function ($record) {
                        return 'Content: '.$record->product?->contentSource->name.'<br>'
                            .'Room Images: '.$record->roomImagesSource->name.'<br>'
                            .'Property Images: '.$record->product?->propertyImagesSource->name;
                    })
                    ->html(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->date()
                    ->sortable(),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('View')
                    ->url(fn (Hotel $record): string => route('hotel-repository.edit', $record))
                    ->visible(fn (Hotel $record) => Gate::allows('update', $record)),
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete')
                    ->visible(fn (Hotel $record): bool => Gate::allows('delete', $record))
                    ->action(function (Hotel $record) {
                        \DB::transaction(function () use ($record) {
                            if ($record->product) {
                                /** @var DeleteProduct $DeleteProductAction */
                                $DeleteProductAction = app(DeleteProduct::class);
                                $DeleteProductAction->handle($record->product);
                            }
                            /** @var DeleteHotel $DeleteHotelAction */
                            $DeleteHotelAction = app(DeleteHotel::class);
                            $DeleteHotelAction->handle($record);
                        });
                        Notification::make()
                            ->title('Hotel deleted successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('addHotelWithGiataCode')
                    ->label('Add Hotel with GIATA Code')
                    ->icon('heroicon-o-document-plus')
                    ->iconButton()
                    ->createAnother(false)
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->action(function (array $data) {
                        $existingHotel = Hotel::where('giata_code', $data['giata_code'])->first();
                        if ($existingHotel) {
                            Notification::make()
                                ->title("Hotel with GIATA code already exists ({$existingHotel->product->name} | {$existingHotel->product->vendor->name})")
                                ->danger()
                                ->send();

                            return;
                        }
                        $this->saveHotelWithGiataCode($data);
                    })
                    ->modalHeading('Add Hotel with GIATA Code')
                    ->modalWidth('4xl')
                    ->form([
                        Section::make('main')->schema([
                            ...HotelForm::getCoreFields(),
                        ]),
                        Section::make('advanced')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Grid::make(3)->schema([
                                    Select::make('main_supplier')
                                        ->label('Main Supplier')
                                        ->required()
                                        ->default(SupplierNameEnum::HOTEL_TRADER->value)
                                        ->options(SupplierNameEnum::contentOptions())
                                        ->columnSpan(1),
                                    Select::make('suppliers')
                                        ->multiple()
                                        ->required()
                                        ->default([SupplierNameEnum::HOTEL_TRADER->value])
                                        ->label('Room Level is taken from the Suppliers')
                                        ->options(SupplierNameEnum::contentOptions())
                                        ->columnSpan(2),
                                    Checkbox::make('auto_marge')
                                        ->label('Auto Merge Different Suppliers')
                                        ->default(true)
                                        ->helperText('Automatically combine numbers from different providers into one unified number code.')
                                        ->columnSpan(3),
                                ]),
                            ]),
                    ])
                    ->visible(! $this->vendor?->id),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('exportDatabase')
                        ->label('Export Database')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function () {
                            if (! $this->exportDatabase()) {
                                return false;
                            }
                            $filePath = 'dump.sql';
                            if (Storage::disk('public')->exists($filePath)) {
                                return response()->download(
                                    Storage::disk('public')->path($filePath),
                                    basename($filePath)
                                );
                            }
                            Notification::make()
                                ->title('File not found')
                                ->danger()
                                ->send();

                            return false;
                        }),
                    Tables\Actions\Action::make('importDatabase')
                        ->label('Import Database')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->form([
                            FileUpload::make('dumpFile')
                                ->label('Select Dump File')
                                ->disk('public')
                                ->directory('database-dumps')
                                ->required(),
                        ])
                        ->action(function (array $data) {
                            $filePath = Storage::disk('public')->path($data['dumpFile']);
                            if (Storage::disk('public')->exists($data['dumpFile'])) {
                                Artisan::call('db:import', [
                                    'file' => $filePath,
                                ]);
                                Notification::make()
                                    ->title('Database imported successfully')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('File not found')
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Tables\Actions\Action::make('exportFiles')
                        ->label('Export Files')
                        ->icon('heroicon-o-chevron-double-down')
                        ->action(function () {
                            $res = Artisan::call('files:export');
                            if ($res > 0) {
                                $fileUrls = '';
                                while ($res > 0) {
                                    $fileUrls .= \URL::to('/storage/files_'.$res.'.zip').PHP_EOL;
                                    $res--;
                                }
                                Notification::make()
                                    ->title('Export Successful')
                                    ->body('Download the file here '.$fileUrls)
                                    ->success()
                                    ->duration(5000)
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Export Error')
                                    ->body('Failed to create archive.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Tables\Actions\Action::make('importFiles')
                        ->label('Import Files')
                        ->icon('heroicon-o-chevron-double-up')
                        ->form([
                            FileUpload::make('zipFiles')
                                ->label('Select Zip File')
                                ->disk('public')
                                ->directory('file-uploads')
                                ->preserveFilenames()
                                ->multiple()
                                ->required(),
                        ])
                        ->action(function (array $data) {
                            $files = $data['zipFiles'];
                            $allSuccess = true;

                            foreach ($files as $file) {
                                $filePath = Storage::disk('public')->path($file);
                                if (Storage::disk('public')->exists($file)) {
                                    $res = Artisan::call('files:import', [
                                        'file' => $filePath,
                                    ]);
                                    if (! $res) {
                                        $allSuccess = false;
                                        break;
                                    }
                                } else {
                                    $allSuccess = false;
                                    break;
                                }
                            }

                            if ($allSuccess) {
                                Notification::make()
                                    ->title('Files imported successfully')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Failed to extract archive')
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])
                    ->label('Database Actions')
                    ->icon('heroicon-o-circle-stack')
                    ->iconButton()
                    ->visible(! $this->vendor?->id),
            ])
            ->filters([
                Filter::make('giata_code')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('giata_code')
                                    ->label('GIATA Code')
                                    ->columnSpan(3),
                                Actions::make([
                                    Action::make('clear_giata')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('giata_code', '');
                                        }),
                                ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['giata_code'])) {
                            $query->where('giata_code', 'like', '%'.$data['giata_code'].'%');
                        }

                        return $query;
                    })
                    ->columnSpan(2),

                Filter::make('product.name')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->columnSpan(3),
                                Actions::make([
                                    Action::make('clear_name')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('name', '');
                                        }),
                                ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['name'])) {
                            $query->whereHas('product', function (Builder $subQuery) use ($data) {
                                $subQuery->where('name', 'like', '%'.$data['name'].'%');
                            });
                        }

                        return $query;
                    })
                    ->columnSpan(2),

                SelectFilter::make('product.attributes')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                Select::make('values')
                                    ->label('Attributes')
                                    ->searchable()
                                    ->multiple()
                                    ->options(function () {
                                        return ConfigAttribute::query()
                                            ->pluck('name', 'id')
                                            ->sortBy(fn ($value, $key) => $value)
                                            ->toArray();
                                    })
                                    ->columnSpan(3),
                                Actions::make([
                                    Action::make('clear_attributes')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('values', '');
                                        }),
                                ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['values'])) {
                            $query->whereHas('product.attributes', function (Builder $subQuery) use ($data) {
                                $subQuery->whereIn('config_attribute_id', $data['values']);
                            });
                        }

                        return $query;
                    })
                    ->columnSpan(2),

                SelectFilter::make('product.verified')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                Select::make('value')
                                    ->label('Verified')
                                    ->options([
                                        1 => 'Yes',
                                        0 => 'No',
                                    ])
                                    ->columnSpan(3),
                                Actions::make([
                                    Action::make('clear_verified')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('value', null);
                                        }),
                                ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value'])) {
                            return $query->whereHas('product', function (Builder $query) use ($data) {
                                $query->where('verified', $data['value']);
                            });
                        }

                        return $query;
                    })
                    ->columnSpan(2),

                SelectFilter::make('product.onSale')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                Select::make('value')
                                    ->label('onSale')
                                    ->options([
                                        1 => 'Yes',
                                        0 => 'No',
                                    ])
                                    ->columnSpan(3),
                                Actions::make([
                                    Action::make('clear_onSale')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('value', null);
                                        }),
                                ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value'])) {
                            return $query->whereHas('product', function (Builder $query) use ($data) {
                                $query->where('onSale', $data['value']);
                            });
                        }

                        return $query;
                    })
                    ->columnSpan(2),

                SelectFilter::make('star_rating')
                    ->form([
                        Grid::make(4)
                            ->schema([
                                Select::make('values')
                                    ->label('Star Rating')
                                    ->multiple()
                                    ->options([
                                        '1-2' => '1-2 Stars',
                                        '3-3.5' => '3-3.5 Stars',
                                        '3.5-4' => '3.5-4 Stars',
                                        '4-4.5' => '4-4.5 Stars',
                                        '4.5-5' => '4.5-5 Stars',
                                        '5-5.5' => '5-5.5 Stars',
                                    ])
                                    ->columnSpan(3),
                                Actions::make([
                                    Action::make('clear_star_rating')
                                        ->icon('heroicon-o-x-mark')
                                        ->iconButton()
                                        ->color('gray')
                                        ->action(function ($state, callable $set) {
                                            $set('values', []);
                                        }),
                                ])
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['values'])) {
                            $query->where(function (Builder $query) use ($data) {
                                foreach ($data['values'] as $value) {
                                    [$min, $max] = explode('-', $value);
                                    $query->orWhereBetween('star_rating', [(float) $min, (float) $max]);
                                }
                            });
                        }

                        return $query;
                    })
                    ->columnSpan(2),
            ], layout: \Filament\Tables\Enums\FiltersLayout::Modal)
            ->filtersFormColumns(4);
    }

    public function exportDatabase(): bool
    {
        $dumpFile = storage_path('app/public/dump.sql');

        Artisan::call('db:export', [
            'prefixes' => 'config_,pd_',
            'tables' => 'activity_log,informational_services',
        ]);

        $timeout = 5;
        $startTime = time();
        while (! file_exists($dumpFile) && (time() - $startTime) < $timeout) {
            usleep(500000);
        }

        if (! file_exists($dumpFile)) {
            Log::error('Error: Dump file not found.');
            Notification::make()
                ->title('Export Error')
                ->body('Dump file not found.')
                ->danger()
                ->send();

            return false;
        }

        Notification::make()
            ->title('Database Export')
            ->body('Dump file created successfully. Download starting.')
            ->success()
            ->send();

        return true;
    }

    public function saveHotelWithGiataCode($data): void
    {
        try {
            /** @var AddHotel $hotelAction */
            $hotelAction = app(AddHotel::class);
            $hotel = $hotelAction->saveWithGiataCode($data);

            Notification::make()
                ->title('Hotel created successfully')
                ->success()
                ->send();

            $this->redirect(route('hotel-repository.edit', $hotel));
        } catch (\Exception $e) {
            Notification::make()
                ->title($e->getMessage())
                ->danger()
                ->send();

            $this->redirect(route('hotel-repository.index'));
        }
    }

    public function render(): View
    {
        return view('livewire.hotels.hotel-table');
    }
}
