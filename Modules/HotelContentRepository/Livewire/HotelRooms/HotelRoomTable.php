<?php

namespace Modules\HotelContentRepository\Livewire\HotelRooms;

use App\Helpers\ClassHelper;
use App\Livewire\Components\CustomRepeater;
use App\Livewire\Configurations\Attributes\AttributesForm;
use App\Models\Configurations\ConfigAttribute;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\Enums\ContentSourceEnum;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\ImageGallery;
use Filament\Forms\Components\RichEditor;

class HotelRoomTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public ?int $hotelId = null;
    public string $title;
    public ?HotelRoom $record = null;

    public function mount(?int $hotelId = null, ?HotelRoom $record = null)
    {
        $this->record = $record;
        $this->hotelId = $hotelId;
        $hotel = Hotel::find($hotelId);
        $this->title = 'Hotel Room for <h4>' . ($hotel ? $hotel->product->name : 'Unknown Hotel') . '</h4>';
    }

    public function schemeForm($record = null): array
    {
        return [
            Hidden::make('hotel_id')->default($this->hotelId),
            Grid::make(3)->schema([
                TextInput::make('name')->label('Name')->required()->columnSpan(2),
                TextInput::make('hbsi_data_mapped_name')->label('External Code')->columnSpan(1),
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
                        'style' => 'max-height: 22em; overflow-x: auto;',
                    ])->columnSpan(2),
                Grid::make(1)->schema([
                    TextInput::make('area')->label('Area, square feet'),
                    TagsInput::make('room_views')->label('Room Views')->placeholder('Enter Views'),
                    TagsInput::make('bed_groups')->label('Bed Types')->placeholder('Enter Bed Types'),
                ])->columnSpan(1),
            ]),
            Grid::make(2)->schema([
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
                    ->options(ImageGallery::pluck('gallery_name', 'id')),
            ]),
            CustomRepeater::make('supplier_codes')
                ->label('Content Suppliers Codes')
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
            ->query(function () {
                $query = HotelRoom::query()->with(['hotel', 'galleries']);
                if ($this->hotelId !== null) {
                    $query->where('hotel_id', $this->hotelId);
                }
                return $query;
            })
            ->columns([
                TextInputColumn::make('hbsi_data_mapped_name')
                    ->label('External Code')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%'])
                    ->disabled(fn () => !Gate::allows('create', Hotel::class)),
                TextInputColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->extraAttributes(['style' => 'width: 100%'])
                    ->disabled(fn () => !Gate::allows('create', Hotel::class)),
                TextColumn::make('supplier_codes')
                    ->label('Supplier Codes')
                    ->formatStateUsing(function ($state) {
                        return implode('<br>', array_map(function ($code) {
                            return $code['supplier'] . ': ' . $code['code'];
                        }, json_decode($state, true) ?? []));
                    })
                    ->html(),
                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->modalWidth('6xl')
                    ->modalHeading(new HtmlString("Edit {$this->title}"))
                    ->tooltip('Edit Hotel Room')
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
                        $record->update($data);
                        if (isset($data['attributes'])) $record->attributes()->sync($data['attributes']);
                        if (isset($data['galleries'])) $record->galleries()->sync($data['galleries']);
                        if (isset($data['supplier_codes'])) $data['supplier_codes'] = json_encode($data['supplier_codes']);

                        Notification::make()
                            ->title('Success')
                            ->body('Hotel room updated successfully.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
                Action::make('add-image')
                    ->icon('heroicon-o-eye')
                    ->tooltip('List Images and Add New Image')
                    ->iconButton()
                    ->modalHeading('Add Image')
                    ->modalWidth('7xl')
                    ->modalContent(function ($record) {
                        return view('dashboard.images.modal', ['productId' => null, 'roomId' => $record->id]);
                    })
                    ->visible(fn () => Gate::allows('create', Hotel::class)),
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
                        if ($this->hotelId) $data['hotel_id'] = $this->hotelId;
                        if (isset($data['supplier_codes'])) $data['supplier_codes'] = json_encode($data['supplier_codes']);
                        $hotelRoom = HotelRoom::create($data);
                        if (isset($data['attributes'])) $hotelRoom->attributes()->sync($data['attributes']);
                        if (isset($data['galleries'])) $hotelRoom->galleries()->sync($data['galleries']);
                        Notification::make()
                            ->title('Success')
                            ->body('Hotel room created successfully.')
                            ->success()
                            ->send();
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
