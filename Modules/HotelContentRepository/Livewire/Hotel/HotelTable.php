<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Helpers\ClassHelper;
use App\Models\Enums\RoleSlug;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Actions\Hotel\AddHotel;
use Modules\HotelContentRepository\Actions\Hotel\DeleteHotel;
use Modules\HotelContentRepository\Actions\Product\DeleteProduct;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Vendor;
use ZipArchive;

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
                            $query->where('vendor_id', auth()->user()->currentTeam->vendor_id);
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

                TextColumn::make('rooms.name')
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('product.verified')
                    ->label('Verified')
                    ->sortable()
                    ->toggleable()
                    ->boolean(),

                TextColumn::make('product.name')
                    ->label('Name')
                    ->searchable()
                    ->toggleable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('giata_code')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

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
                    ->searchable()
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('num_rooms')
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
                        $this->saveHotelWithGiataCode($data);
                    })
                    ->modalHeading('Add Hotel with GIATA Code')
                    ->modalWidth('lg')
                    ->form(HotelForm::getCoreFields()),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('exportDatabase')
                        ->label('Export Database')
                        ->icon('heroicon-o-arrow-up-tray')
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
                        ->icon('heroicon-o-arrow-down-tray')
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
                        ->icon('heroicon-o-chevron-double-up')
                        ->action(function () {
                            $zip = new ZipArchive;
                            $zipFile = storage_path('app/public/files.zip');

                            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                                $directories = ['products', 'images'];
                                foreach ($directories as $directory) {
                                    $files = Storage::disk('public')->files($directory);
                                    foreach ($files as $file) {
                                        $zip->addFile(storage_path('app/public/'.$file), $file);
                                    }
                                }
                                $zip->close();

                                return response()->download($zipFile);
                            } else {
                                Notification::make()
                                    ->title('Failed to create archive')
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Tables\Actions\Action::make('importFiles')
                        ->label('Import Files')
                        ->icon('heroicon-o-chevron-double-down')
                        ->form([
                            FileUpload::make('zipFile')
                                ->label('Select Zip File')
                                ->disk('public')
                                ->directory('file-uploads')
                                ->required(),
                        ])
                        ->action(function (array $data) {
                            $zipFile = Storage::disk('public')->path($data['zipFile']);
                            $zip = new ZipArchive;

                            if ($zip->open($zipFile) === true) {
                                $zip->extractTo(storage_path('app/public'));
                                $zip->close();

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
                    ->visible(fn () => env('EXPORT_BD_USE', false)),
            ]);
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
