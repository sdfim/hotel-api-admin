<?php

namespace Modules\HotelContentRepository\Livewire\Activity;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class ActivityTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(5)
            ->query(Activity::query()->orderBy('created_at', 'desc'))
            ->columns([
                //                TextColumn::make('id')->label('ID'),

                TextColumn::make('causer.name')->label('Changed By'),
                TextColumn::make('causer.email')->label('Email'),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->badge()
                    ->colors([
                        'primary' => 'created',
                        'warning' => 'updated',
                        'danger' => 'deleted',
                    ])
                    ->width('200px'),

                TextColumn::make('log_name')->label('Log Name'),

                TextColumn::make('subject_type')
                    ->label('Model Name')
                    ->formatStateUsing(fn ($state) => class_basename($state)),

                TextColumn::make('properties')
                    ->label('Changed Attribute')
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

                TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn ($record) => route('activities.show', $record)),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public function render()
    {
        return view('livewire.activity.activity-table');
    }
}
