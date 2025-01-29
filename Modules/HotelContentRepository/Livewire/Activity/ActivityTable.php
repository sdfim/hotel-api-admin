<?php

namespace Modules\HotelContentRepository\Livewire\Activity;

use App\Helpers\ClassHelper;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
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
            ->query(Activity::query())
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('description')->label('Description'),
                TextColumn::make('log_name')->label('Log Name'),
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
