<?php

namespace App\Livewire\Activity;

use App\Models\Channel;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Livewire\Component;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\ImageGallery;
use Modules\HotelContentRepository\Models\KeyMapping;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAffiliation;
use Modules\HotelContentRepository\Models\ProductAttribute;
use Modules\HotelContentRepository\Models\ProductDepositInformation;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;
use Modules\HotelContentRepository\Models\Vendor;
use Spatie\Activitylog\Models\Activity;

class ActivityTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    private $level;

    private $subjectId;

    private ?Hotel $hotel;

    private ?Product $product;

    private ?Vendor $vendor;

    public function mount(?string $level = null, ?int $id = null)
    {
        $this->level = $level;
        $this->subjectId = $id;
        $this->hotel = null;
        $this->product = null;
        $this->vendor = null;

        if ($this->level === 'Product') {
            $this->product = Product::find($this->subjectId);
            $this->hotel = $this->product->related;
        } elseif ($this->level === 'Vendor') {
            $this->vendor = Vendor::find($this->subjectId);
        }

        session([
            'activity_level' => $this->level,
            'activity_product' => $this->product,
            'activity_hotel' => $this->hotel,
            'activity_vendor' => $this->vendor,
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->persistSearchInSession()
            ->query(Activity::query()->with(['causer' => function ($query) {
                $query->withTrashed();
            }]))
            ->modifyQueryUsing(function ($query) {
                $level = session('activity_level', $this->level);

                if ($level === 'Product') {
                    $product = session('activity_product', $this->product ?? null);
                    $hotel = session('activity_hotel', $this->hotel ?? null);

                    $query->where(function ($query) use ($product, $hotel) {
                        $query->where('subject_type', Product::class)
                            ->where('subject_id', $product->getAttribute('id'));
                        $channelIds = $product->channels->pluck('id')->toArray();
                        $affiliationIds = $product->affiliations->pluck('id')->toArray();
                        $attributeIds = $product->attributes()->pluck('id')->toArray();
                        $contentSectionIds = $product->descriptiveContentsSection->pluck('id')->toArray();
                        $keyMappingIds = $product->keyMappings->pluck('id')->toArray();
                        $galleryIds = $product->galleries->pluck('id')->toArray();
                        $depositInfoIds = $product->depositInformations->pluck('id')->toArray();
                        if (! empty($channelIds)) {
                            $query->orWhere('subject_type', Channel::class)
                                ->whereIn('subject_id', $channelIds);
                        }
                        if (! empty($affiliationIds)) {
                            $query->orWhere('subject_type', ProductAffiliation::class)
                                ->whereIn('subject_id', $affiliationIds);
                        }
                        if (! empty($attributeIds)) {
                            $query->orWhere('subject_type', ProductAttribute::class)
                                ->whereIn('subject_id', $attributeIds);
                        }
                        if (! empty($contentSectionIds)) {
                            $query->orWhere('subject_type', ProductDescriptiveContentSection::class)
                                ->whereIn('subject_id', $contentSectionIds);
                        }
                        if (! empty($keyMappingIds)) {
                            $query->orWhere('subject_type', KeyMapping::class)
                                ->whereIn('subject_id', $keyMappingIds);
                        }
                        if (! empty($galleryIds)) {
                            $query->orWhere('subject_type', ImageGallery::class)
                                ->whereIn('subject_id', $galleryIds);
                        }
                        if (! empty($depositInfoIds)) {
                            $query->orWhere('subject_type', ProductDepositInformation::class)
                                ->whereIn('subject_id', $depositInfoIds);
                        }

                        $query->orWhere('subject_type', Hotel::class)
                            ->where('subject_id', $hotel->getAttribute('id'));
                        $roomIds = $hotel->rooms->pluck('id')->toArray();
                        $rateIds = $hotel->rates->pluck('id')->toArray();
                        $webFindersIds = $hotel->webFinders->pluck('id')->toArray();
                        if (! empty($roomIds)) {
                            $query->orWhereIn('subject_id', $roomIds)
                                ->where('subject_type', HotelRoom::class);
                        }
                        if (! empty($rateIds)) {
                            $query->orWhereIn('subject_id', $rateIds)
                                ->where('subject_type', HotelRate::class);
                        }
                        if (! empty($webFindersIds)) {
                            $query->orWhereIn('subject_id', $webFindersIds)
                                ->where('subject_type', HotelWebFinder::class);
                        }
                    });

                }

                if ($level === 'Vendor') {
                    $vendor = session('activity_vendor', $this->vendor ?? null);
                    $query
                        ->where('subject_type', Vendor::class)
                        ->where('subject_id', $vendor->getAttribute('id'));
                }

                return $query;
            })
            ->defaultSort('created_at', 'DESC')
            ->columns([
                TextColumn::make('causer.name')->label('Changed By'),
                TextColumn::make('causer.email')->label('Email'),
                TextColumn::make('description')
                    ->label('Description')
                    ->badge()
                    ->colors([
                        'primary' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                    ])
                    ->width('200px'),
                TextColumn::make('log_name')->label('Log Name')->searchable(),
                TextColumn::make('subject_type')
                    ->label('Model Name')
                    ->formatStateUsing(fn ($state) => class_basename($state)),
                TextColumn::make('properties')
                    ->label('Changed Attribute')
                    ->wrap()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        $jsonObjects = explode(', ', $state);
                        $new = Arr::get($jsonObjects, 1, '');
                        $properties = json_decode($new, true);
                        $formattedProperties = collect($properties)->reject(function ($value, $key) {
                            return $key === 'updated_at';
                        })->take(3)->map(function ($value, $key) {
                            if (is_array($value)) {
                                return "$key";
                            }

                            return "$key: $value";
                        })->implode(', ');

                        return $formattedProperties;
                    }),
                TextColumn::make('created_at')->label('Created At')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('description')
                    ->multiple()
                    ->options([
                        'created' => 'Create',
                        'updated' => 'Update',
                        'deleted' => 'Delete',
                    ])
                    ->label('Filter by Description'),
            ])
            ->actions([
                Action::make('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('View Activity')
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->closeModalByClickingAway(false)
                    ->modalContent(function (Activity $record) {
                        return view('dashboard.activities.show-modal', ['activity' => $record]);
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.activity.activity-table');
    }
}
