<?php

namespace Modules\HotelContentRepository\Livewire\Hotel;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;
use Modules\HotelContentRepository\Filament\CustomTextColumn;
use Modules\HotelContentRepository\Models\Hotel;

class HotelTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->schemeForm());
    }

    public function schemeForm(): array
    {
        return [
            TextInput::make('name')->label('Name')->required(),
            TextInput::make('type')->label('Type')->required(),
            TextInput::make('address')->label('Address')->required(),
            TextInput::make('star_rating')->label('Star Rating')->required(),
            TextInput::make('website')->label('Website')->required(),
            TextInput::make('num_rooms')->label('Number of Rooms')->required(),
            TextInput::make('featured')->label('Featured')->required(),
            TextInput::make('location')->label('Location')->required(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(Hotel::with([
                'affiliations',
                'attributes',
                'contentSource',
                'roomImagesSource',
                'propertyImagesSource',
                'descriptiveContentsSection',
                'feeTaxes',
                'informativeServices',
                'promotions',
                'rooms',
                'keyMappings',
                'galleries'
            ]))
            ->columns([
                TextColumn::make('name')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->wrap()
                    ->tooltip(function ($record) {
                        return implode("\n", [
                            'Verified: ' . ($record->verified ? 'Yes' : 'No'),
                            'Direct Connection: ' . ($record->direct_connection ? 'Yes' : 'No'),
                            'Manual Contract: ' . ($record->manual_contract ? 'Yes' : 'No'),
                            'Commission Tracking: ' . ($record->commission_tracking ? 'Yes' : 'No'),
                            'Channel Management: ' . ($record->channel_management ? 'Yes' : 'No'),
                        ]);
                    }),
                CustomTextColumn::make('type')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                CustomTextColumn::make('address')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                CustomTextColumn::make('star_rating')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
//                CustomTextColumn::make('website')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
                CustomTextColumn::make('num_rooms')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
//                CustomTextColumn::make('featured')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
                CustomTextColumn::make('location')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
                CustomTextColumn::make('combined_sources')
                    ->label('Combined Sources')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable()
                    ->default(function ($record) {
                        return $record->contentSource->name . ' ' . $record->roomImagesSource->name . ' ' . $record->propertyImagesSource->name;
                    }),
//                CustomTextColumn::make('hotel_board_basis')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('default_currency')
//                    ->label('Currency')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('affiliations')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('attributes')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('contentSource.name')
//                    ->label('Content Source')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('roomImagesSource.name')
//                    ->label('Room Images Source')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('propertyImagesSource.name')
//                    ->label('Property Images Source')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('descriptiveContentsSection')
//                    ->label('Descriptive Contents')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('feeTaxes')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('informativeServices')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('promotions')
//                    ->label('Promotions Galleries')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('rooms')
//                    ->label('Rooms Galleries')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
//                CustomTextColumn::make('keyMappings')
//                    ->searchable(isIndividual: true)
//                    ->toggleable()
//                    ->sortable(),
                CustomTextColumn::make('galleries')
                    ->label('Galleries')
                    ->searchable(isIndividual: true)
                    ->toggleable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->tooltip('View')
                    ->url(fn (Hotel $record): string => route('hotel_repository.edit', $record))
//                    ->visible(fn (Hotel $record) => Gate::allows('update', $record))
                ,
                Tables\Actions\DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete')
                    ->visible(fn (Hotel $record): bool => Gate::allows('delete', $record)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form((new HotelForm())->schemeForm())
                    ->visible(Gate::allows('create', Hotel::class))
                    ->tooltip('Add New Hotel')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.hotels.hotel-table');
    }
}
