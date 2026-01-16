<?php

namespace App\Livewire;

use App\Models\Notification;
use Exception;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class NotificationTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10])
            ->query(Notification::query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('data.title')
                    ->label('Title')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('data.body')
                    ->wrap()
                    ->label('Message')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('notifiable.name')
                    ->label('User')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('data.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'error', 'danger' => 'danger',
                        'warning' => 'warning',
                        'info' => 'info',
                        default => 'gray',
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'success' => 'Success',
                        'error' => 'Error',
                        'info' => 'Info',
                        'warning' => 'Warning',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === 'error') {
                            return $query->whereIn('data->status', ['error', 'danger']);
                        }

                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $state): Builder => $query->where('data->status', $state)
                        );
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.notification-table');
    }
}
