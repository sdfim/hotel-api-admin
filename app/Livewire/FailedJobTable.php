<?php

namespace App\Livewire;

use App\Models\FailedJob;
use Exception;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Livewire\Component;

class FailedJobTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->paginated([5, 10, 25, 50])
            ->query(FailedJob::query())
            ->defaultSort('failed_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('connection')
                    ->label('Connection')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('queue')
                    ->label('Queue')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('payload')
                    ->label('Payload')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->payload)
                    ->toggleable(),
                TextColumn::make('exception')
                    ->label('Exception')
                    ->limit(100)
                    ->wrap()
                    ->tooltip(fn ($record) => $record->exception)
                    ->toggleable(),
                TextColumn::make('failed_at')
                    ->label('Failed At')
                    ->sortable()
                    ->toggleable(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('retry')
                        ->label('Retry')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (FailedJob $record) {
                            try {
                                Artisan::call('queue:retry', ['id' => [$record->uuid]]);
                                Notification::make()
                                    ->title('Job retried successfully')
                                    ->success()
                                    ->send();
                            } catch (Exception $e) {
                                Notification::make()
                                    ->title('Failed to retry job')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                    DeleteAction::make(),
                    Action::make('view_payload')
                        ->label('Payload')
                        ->icon('heroicon-o-eye')
                        ->modalContent(fn (FailedJob $record): View => view(
                            'livewire.failed-jobs.payload-modal',
                            ['payload' => $record->payload],
                        ))
                        ->modalWidth('7xl')
                        ->modalSubmitAction(false),
                    Action::make('view_exception')
                        ->label('Exception')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->modalContent(fn (FailedJob $record): View => view(
                            'livewire.failed-jobs.exception-modal',
                            ['exception' => $record->exception],
                        ))
                        ->modalWidth('7xl')
                        ->modalSubmitAction(false),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('retry_selected')
                    ->label('Retry Selected')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $uuids = $records->pluck('uuid')->toArray();
                        try {
                            Artisan::call('queue:retry', ['id' => $uuids]);
                            Notification::make()
                                ->title('Selected jobs retried successfully')
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Failed to retry selected jobs')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->headerActions([
                Action::make('retry_all')
                    ->label('Retry All')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function () {
                        try {
                            Artisan::call('queue:retry', ['id' => ['all']]);
                            Notification::make()
                                ->title('All failed jobs have been queued for retry')
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Failed to retry all jobs')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('flush_all')
                    ->label('Delete All')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        try {
                            Artisan::call('queue:flush');
                            Notification::make()
                                ->title('All failed jobs have been deleted')
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Failed to delete all jobs')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.failed-job-table');
    }
}
