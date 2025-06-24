<?php

namespace App\Livewire;

use App\Models\ScheduledTask;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ScheduledTasksTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(ScheduledTask::query())
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('description')->wrap(),
                TextColumn::make('command')->searchable(),
                TextColumn::make('frequency_type')->sortable(),
                TextColumn::make('day_of_week')
                    ->formatStateUsing(fn ($state) => $this->getDayName($state))
                    ->visible(fn ($record) => $record && $record->frequency_type === 'weekly'),
                TextColumn::make('time'),
                ToggleColumn::make('is_active'),
            ])
            ->filters([
                // You can add filters here as needed
            ])
            ->actions([
                EditAction::make()->form($this->getFormSchema()),
            ])
            ->bulkActions([
                // You can add bulk actions here as needed
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema());
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->maxLength(255),
            TextInput::make('command')
                ->required()
                ->disabled()
                ->maxLength(255),
            Select::make('frequency_type')
                ->options([
                    'weekly' => 'Weekly',
                    'daily' => 'Daily',
                    'hourly' => 'Hourly',
                    'custom' => 'Custom Cron',
                ])
                ->default('weekly')
                ->reactive()
                ->required(),
            Select::make('day_of_week')
                ->options([
                    0 => 'Sunday',
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                ])
                ->visible(fn ($get) => $get('frequency_type') === 'weekly')
                ->required(fn ($get) => $get('frequency_type') === 'weekly'),
            TextInput::make('time')
                ->required()
                ->placeholder('HH:MM')
                ->mask('99:99')
                ->visible(fn ($get) => in_array($get('frequency_type'), ['weekly', 'daily'])),
            TextInput::make('cron_expression')
                ->required(fn ($get) => $get('frequency_type') === 'custom')
                ->visible(fn ($get) => $get('frequency_type') === 'custom')
                ->placeholder('* * * * *')
                ->helperText('Format: minute hour day month weekday'),
            Toggle::make('is_active')
                ->default(true)
                ->label('Active'),
        ];
    }

    public function getDayName($day)
    {
        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return $days[$day] ?? 'Unknown';
    }

    public function render()
    {
        return view('livewire.scheduled-tasks-table');
    }
}
