<?php

namespace Modules\HotelContentRepository\Livewire\HotelRooms;

use App\Helpers\ClassHelper;
use App\Livewire\Components\CustomRepeater;
use App\Livewire\Configurations\Attributes\AttributesForm;
use App\Livewire\Configurations\RoomBedTypes\RoomBedTypeForm;
use App\Models\Configurations\ConfigAttribute;
use App\Models\Configurations\ConfigRoomBedType;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\Enums\ContentSourceEnum;
use Modules\HotelContentRepository\Actions\HotelRoom\AddHotelRoom;
use Modules\HotelContentRepository\Actions\HotelRoom\EditHotelRoom;
use Modules\HotelContentRepository\Actions\HotelRoom\MergeHotelRoom;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;

class HotelRoomTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $hotelId = null;

    public string $title;

    public HotelRoom $fromRoom;

    public HotelRoom $toRoom;

    public array $mergeOptions;

    public array $mergeSelectedOption;

    public ?HotelRoom $record = null;

    public function mount(Hotel $hotel, ?HotelRoom $record = null)
    {
        $this->record = $record;
        $this->hotelId = $hotel->id;
        $this->title = 'Hotel Room for '.$hotel->product->name;
    }

    public function schemeForm($record = null): array
    {
        return [
            Tabs::make('Tabs')
                ->extraAttributes(['class' => 'custom-tabs-class', 'style' => 'background-color: #f5f5f5;'])
                ->tabs([
                    Tabs\Tab::make('Main')
                        ->schema([
                            Hidden::make('hotel_id')->default($this->hotelId),
                            Grid::make(3)->schema([
                                TextInput::make('name')->label('Name')->required()->columnSpan(2),
                                TextInput::make('external_code')->label('External Code')->columnSpan(1),
                            ]),
                            Grid::make(3)->schema([
                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(15)
                                    ->required()
                                    ->extraAttributes([
                                        'class' => 'h-40',
                                    ])->columnSpan(2),
                                Grid::make(1)->schema([
                                    TextInput::make('area')
                                        ->label('Area, square feet')
                                        ->inlineLabel(),
                                    TextInput::make('max_occupancy')
                                        ->label('Max Occupancy')
                                        ->placeholder('Enter Max Occupancy')
                                        ->inlineLabel(),
                                    TagsInput::make('room_views')->label('Room Views')->placeholder('Enter Views'),
                                    Select::make('bed_groups')
                                        ->label('Bed Types')
                                        ->multiple()
                                        ->searchable()
                                        ->native(false)
                                        ->createOptionForm(Gate::allows('create', ConfigRoomBedType::class) ? RoomBedTypeForm::getSchema() : [])
                                        ->createOptionUsing(function (array $data) {
                                            $bedType = ConfigRoomBedType::create($data);
                                            Notification::make()
                                                ->title('Bed Type created successfully')
                                                ->success()
                                                ->send();

                                            return $bedType->id;
                                        })
                                        ->options(ConfigRoomBedType::pluck('name', 'name')),
                                    Select::make('related_rooms')
                                        ->label('Connecting Room Types')
                                        ->multiple()
                                        ->options(function (callable $get) {
                                            $hotelId = $get('hotel_id');

                                            return HotelRoom::where('hotel_id', $hotelId)
                                                ->orderBy('name')
                                                ->pluck('name', 'id');
                                        }),
                                ])->columnSpan(1),
                            ]),

                            CustomRepeater::make('supplier_codes')
                                ->label('Supplier Room Codes')
                                ->schema([
                                    Grid::make(3)->schema([
                                        Select::make('supplier')
                                            ->placeholder('Select Supplier')
                                            ->label(fn ($get) => $get('supplier_codes.0.supplier') ? 'Supplier' : false)
                                            ->options(ContentSourceEnum::options()),
                                        TextInput::make('code')
                                            ->placeholder('Enter Code')
                                            ->label(fn ($get) => $get('supplier_codes.0.code') ? 'Code' : false),
                                        TextInput::make('name')
                                            ->placeholder('Enter Name')
                                            ->label(fn ($get) => $get('supplier_codes.0.name') ? 'Name' : false),
                                    ]),
                                ]),
                        ]),
                    Tabs\Tab::make('Attributes and Galleries')
                        ->schema([
                            Grid::make(1)->schema([
                                Select::make('attributes')
                                    ->label('Attributes')
                                    ->createOptionForm(Gate::allows('create', ConfigAttribute::class) ? AttributesForm::getSchema() : [])
                                    ->createOptionUsing(function (array $data) {
                                        $data['default_value'] = '';
                                        $attribute = ConfigAttribute::create($data);
                                        Notification::make()
                                            ->title('Attributes created successfully')
                                            ->success()
                                            ->send();

                                        return $attribute->id;
                                    })
                                    ->searchable()
                                    ->multiple()
                                    ->native(false)
                                    ->options(ConfigAttribute::all()->sortBy('name')->pluck('name', 'id')),

                                Select::make('galleries')
                                    ->label('Galleries')
                                    ->multiple()
                                    ->relationship('galleries', 'gallery_name')
                                    ->searchable()
                                    ->native(false),
                            ]),
                        ]),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->query(HotelRoom::query())
            ->modifyQueryUsing(function ($query) {
                if ($this->hotelId !== null) {
                    $query->where('hotel_id', $this->hotelId);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('Merge')
                    ->searchable()
                    ->icon('heroicon-o-arrows-up-down')
                    ->formatStateUsing(fn ($state) => "<span class='hidden'>{$state}</span>")
                    ->html(),

                TextColumn::make('id_display')
                    ->label('Room ID')
                    ->getStateUsing(fn ($record) => $record->id),

                TextColumn::make('external_code')
                    ->label('External Code')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%'])
                    ->disabled(fn () => ! Gate::allows('update', Hotel::class)),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%'])
                    ->disabled(fn () => ! Gate::allows('update', Hotel::class)),

                TextColumn::make('supplier_codes')
                    ->label('Supplier Room Codes')
                    ->formatStateUsing(function ($state) {
                        return implode('<br>', array_map(function ($code) {
                            return $code['supplier'].': '.$code['code'];
                        }, json_decode($state, true) ?? []));
                    })
                    ->html(),

                TextColumn::make('galleries_count')
                    ->label('Images')
                    ->badge()
                    ->colors([
                        'gray' => 0,
                        'success' => fn (string $state): bool => $state > 0,
                    ])
                    ->getStateUsing(fn (?HotelRoom $record): int => $record ? $record->galleries->flatMap(fn ($gallery) => $gallery->images)->count() : 0),

                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                ActionGroup::make([

                    EditAction::make()
                        ->label('Edit Room')
                        ->icon('heroicon-o-pencil')
                        ->modalWidth('7xl')
                        ->modalHeading(new HtmlString("Edit {$this->title}"))
                        ->form($this->schemeForm())
                        ->modalFooterActions(function ($livewire, $action) {
                            return Gate::allows('create', Hotel::class)
                                ? [$action->getModalSubmitAction(), $action->getModalCancelAction()]
                                : [$action->getModalCancelAction()];
                        })
                        ->fillForm(function ($record) {
                            $data = $record->toArray();
                            $data['galleries'] = $record->galleries->pluck('id')->toArray();
                            $data['attributes'] = $record->attributes->pluck('id')->toArray();
                            $data['hotel_id'] = $record->hotel->id;
                            $data['supplier_codes'] = json_decode($record->supplier_codes, true);

                            return $data;
                        })
                        ->action(function (HotelRoom $record, array $data) {
                            /** @var EditHotelRoom $editHotelRoom */
                            $editHotelRoom = app(EditHotelRoom::class);
                            $editHotelRoom->execute($record, $data);
                            Notification::make()
                                ->title('Success')
                                ->body('Hotel room updated successfully.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => Gate::allows('update', Hotel::class)),

                    Action::make('images')
                        ->icon('heroicon-o-gif')
                        ->label('Images')
                        ->modalHeading('Add Image')
                        ->modalWidth('screen-2xl')
                        ->modalSubmitAction(false)
                        ->modalContent(function ($record) {
                            return view('dashboard.images.modal', ['productId' => null, 'roomId' => $record->id]);
                        })
                        ->visible(fn () => Gate::allows('update', Hotel::class)),

                    Action::make('rollback')
                        ->label('Rollback Merge')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->action(function ($record) {
                            /** @var MergeHotelRoom $mergeHotelRoom */
                            $mergeHotelRoom = app(MergeHotelRoom::class);
                            $success = $mergeHotelRoom->rollback($record);

                            if ($success) {
                                Notification::make()
                                    ->title('Success')
                                    ->body('Merge rollback successful.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Merge rollback failed.')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn ($record) => Gate::allows('update', Hotel::class) && $record->isMergedRoom),

                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->visible(fn () => Gate::allows('update', Hotel::class)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->modalWidth('5xl')
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->action(function ($data) {
                        /** @var AddHotelRoom $addHotelRoom */
                        $addHotelRoom = app(AddHotelRoom::class);
                        $hotelRoom = $addHotelRoom->create($data, $this->hotelId);
                        if ($hotelRoom !== null) {
                            Notification::make()
                                ->title('Success')
                                ->body('Hotel room created successfully.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Error')
                                ->body('Hotel room not created.')
                                ->send();
                        }
                    })
                    ->createAnother(false)
                    ->tooltip('Add New Room')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
            ])
            ->filters([
                SelectFilter::make('room_type')
                    ->label('Room Type')
                    ->options([
                        'merged' => 'Merged',
                        'primary' => 'Primary',
                        'secondary' => 'Secondary',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['value'])) {
                            return match ($data['value']) {
                                'merged' => $query->whereHas('newMerge'),
                                'secondary' => $query->whereHas('crm'),
                                'primary' => $query->whereDoesntHave('newMerge')->whereDoesntHave('crm'),
                                default => $query,
                            };
                        }

                        return $query;
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-room-table');
    }

    public function mergeRooms($fromId, $toId)
    {
        $this->fromRoom = HotelRoom::find($fromId);
        $this->toRoom = HotelRoom::find($toId);

        if ($this->fromRoom && $this->toRoom) {
            $this->dispatch('open-merge-confirmation-modal');
        }
    }

    public function confirmMerge(): void
    {
        $fromRoom = HotelRoom::find($this->fromRoom->id);
        $toRoom = HotelRoom::find($this->toRoom->id);

        if (! $fromRoom->isValidForMerge(true) || ! $toRoom->isValidForMerge(false)) {
            Notification::make()
                ->title('Error')
                ->body('Rooms are not valid for merging due to the following reasons:
                - One or both rooms are already merged.
                - The primary entity is not a CRM record.')
                ->danger()
                ->send();

            return;
        }

        if ($this->fromRoom && $this->toRoom) {

            /* @var MergeHotelRoom $mergeHotelRoom */
            $mergeHotelRoom = app(MergeHotelRoom::class);
            $newRoom = $mergeHotelRoom->execute($this->toRoom, $this->fromRoom, $this->mergeSelectedOption);

            Notification::make()
                ->title('Success')
                ->body('Hotel rooms merged successfully. New Room ID: '.$newRoom->id.', Name: '.$newRoom->name)
                ->success()
                ->send();

        }
    }
}
