<?php

namespace Modules\HotelContentRepository\Livewire\HotelRooms;

use App\Helpers\ClassHelper;
use App\Livewire\Components\CustomRepeater;
use App\Livewire\Configurations\Attributes\AttributesForm;
use App\Models\Configurations\ConfigAttribute;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
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
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\Enums\ContentSourceEnum;
use Modules\HotelContentRepository\Actions\HotelRoom\AddHotelRoom;
use Modules\HotelContentRepository\Actions\HotelRoom\EditHotelRoom;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\ImageGallery;

class HotelRoomTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $hotelId = null;

    public string $title;

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
            Hidden::make('hotel_id')->default($this->hotelId),
            Grid::make(3)->schema([
                TextInput::make('name')->label('Name')->required()->columnSpan(2),
                TextInput::make('external_code')->label('UJV Code')->columnSpan(1),
            ]),
            Grid::make(3)->schema([
                RichEditor::make('description')
                    ->label('Description')
                    ->required()
                    ->disableAllToolbarButtons()
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->extraAttributes([
                        'style' => 'max-height: 30em; overflow-x: auto;',
                    ])->columnSpan(2),
                Grid::make(1)->schema([
                    TextInput::make('area')->label('Area, square feet'),
                    TagsInput::make('room_views')->label('Room Views')->placeholder('Enter Views'),
                    TagsInput::make('bed_groups')->label('Bed Types')->placeholder('Enter Bed Types'),
                    Select::make('related_rooms')
                        ->label('Connecting Room Types')
                        ->multiple()
                        ->options(function (callable $get) {
                            $hotelId = $get('hotel_id');

                            return HotelRoom::where('hotel_id', $hotelId)
                                ->pluck('name', 'id');
                        }),
                ])->columnSpan(1),
            ]),
            Grid::make(1)->schema([
                Select::make('attributes')
                    ->label('Attributes')
                    ->createOptionForm(AttributesForm::getSchema())
                    ->createOptionUsing(function (array $data) {
                        $data['default_value'] = '';
                        ConfigAttribute::create($data);
                        Notification::make()
                            ->title('Attributes created successfully')
                            ->success()
                            ->send();
                    })
                    ->searchable()
                    ->multiple()
                    ->options(ConfigAttribute::pluck('name', 'id')),
                Select::make('galleries')
                    ->label('Galleries')
                    ->multiple()
                    ->relationship('galleries', 'gallery_name')
                    ->searchable()
                    ->native(false),
            ]),
            CustomRepeater::make('supplier_codes')
                ->label('Supplier Codes')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('supplier')
                            ->placeholder('Select Supplier')
                            ->label(fn ($get) => $get('supplier_codes.0.supplier') ? 'Supplier' : false)
                            ->options(ContentSourceEnum::options()),
                        TextInput::make('code')
                            ->placeholder('Enter Code')
                            ->label(fn ($get) => $get('supplier_codes.0.code') ? 'Code' : false),
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
                TextInputColumn::make('external_code')
                    ->label('UJV Code')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%'])
                    ->disabled(fn () => ! Gate::allows('create', Hotel::class)),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%'])
                    ->disabled(fn () => ! Gate::allows('create', Hotel::class)),
                TextColumn::make('supplier_codes')
                    ->label('Supplier Codes')
                    ->formatStateUsing(function ($state) {
                        return implode('<br>', array_map(function ($code) {
                            return $code['supplier'].': '.$code['code'];
                        }, json_decode($state, true) ?? []));
                    })
                    ->html(),
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
                        ->visible(fn () => Gate::allows('create', Hotel::class)),

                    Action::make('images')
                        ->icon('heroicon-o-gif')
                        ->label('Images')
                        ->modalHeading('Add Image')
                        ->modalWidth('screen-2xl')
                        ->modalSubmitAction(false)
                        ->modalContent(function ($record) {
                            return view('dashboard.images.modal', ['productId' => null, 'roomId' => $record->id]);
                        })
                        ->visible(fn () => Gate::allows('create', Hotel::class)),

                    Action::make('add-attributes')
                        ->icon('heroicon-o-gift')
                        ->label('Ultimate Amenities')
                        ->modalHeading(fn ($record) => 'Add Amenities For Room: '.$record->name)
                        ->modalWidth('7xl')
                        ->modalSubmitAction(false)
                        ->modalContent(function ($record) {
                            return view('dashboard.hotel_repository.hotel_rooms.modal_attributes', [
                                'product' => $record->hotel->product,
                                'roomId' => $record->id,
                            ]);
                        })
                        ->visible(fn () => Gate::allows('create', Hotel::class)),

                    Action::make('add-fee-tax')
                        ->icon('heroicon-o-banknotes')
                        ->label('Fees and Taxes')
                        ->modalHeading(fn ($record) => 'Add Fees and Taxes For Room: '.$record->name)
                        ->modalWidth('full')
                        ->modalSubmitAction(false)
                        ->modalContent(function ($record) {
                            return view('dashboard.hotel_repository.hotel_rooms.modal_fee_tax', [
                                'product' => $record->hotel->product,
                                'roomId' => $record->id,
                            ]);
                        })
                        ->visible(fn () => Gate::allows('create', Hotel::class)),

                    Action::make('add-informational-service')
                        ->icon('heroicon-o-sparkles')
                        ->label('Hotel Service')
                        ->modalHeading(fn ($record) => 'Add Informational Services For Room: '.$record->name)
                        ->modalWidth('7xl')
                        ->modalSubmitAction(false)
                        ->modalContent(function ($record) {
                            return view('dashboard.hotel_repository.hotel_rooms.modal_informative_services', [
                                'product' => $record->hotel->product,
                                'roomId' => $record->id,
                            ]);
                        })
                        ->visible(fn () => Gate::allows('create', Hotel::class)),

                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
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
            ]);
    }

    public function render()
    {
        return view('livewire.hotels.hotel-room-table');
    }
}
