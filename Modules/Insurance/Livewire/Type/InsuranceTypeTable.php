<?php

namespace Modules\Insurance\Livewire\Type;

use App\Livewire\Components\CustomRepeater;
use App\Models\Enums\RoleSlug;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceType;

class InsuranceTypeTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function form(Form $form): Form
    {
        return $form->schema($this->schemeForm());
    }

    public function schemeForm(?InsuranceType $record = null): array
    {
        return [

            TextInput::make('name')
                ->label('Name')
                ->required()
                ->unique(ignorable: $record),

            CustomRepeater::make('benefits')
                ->label('Benefits')
                ->schema([
                    TextInput::make('type')
                        ->label(false)
                        ->placeholder('Benefit'),
                    TextInput::make('amount')
                        ->label(false)
                        ->placeholder('Amount'),
                ])
                ->required()
                ->columns(2),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                InsuranceType::query()
                    ->when(
                        auth()->user()->currentTeam && ! auth()->user()->hasRole(RoleSlug::ADMIN->value),
                        fn ($q) => $q->where('vendor_id', auth()->user()->currentTeam->vendor_id),
                    )
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('benefits')
                    ->label('Benefits')
                    ->formatStateUsing(function ($state) {
                        $benefits = explode(', ', $state);
                        $str = '';
                        foreach ($benefits as $benefit) {
                            $benefit = json_decode($benefit, true);
                            $str .= Arr::get($benefit, 'type').' - <b>'.Arr::get($benefit, 'amount').'</b><br>';
                        }

                        return $str;
                    })
                    ->html()
                    ->searchable(),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->tooltip('Edit Insurance Type')
                    ->form(fn (InsuranceType $record) => $this->schemeForm($record))
                    ->fillForm(function (InsuranceType $record) {
                        return $record->toArray();
                    })
//                    ->visible(fn (InsuranceType $record): bool => Gate::allows('update', $record))
                    ->action(function (InsuranceType $record, array $data) {
                        $record->update($data);

                        Notification::make()
                            ->title('Updated successfully')
                            ->success()
                            ->send();

                        return $data;
                    }),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Delete Insurance Type')
                    ->requiresConfirmation()
//                    ->visible(fn (InsuranceType $record): bool => Gate::allows('delete', $record))
                    ->action(function (InsuranceType $record) {
                        $record->delete();

                        Notification::make()
                            ->title('Deleted successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->schemeForm())
                    ->action(function (array $data) {
                        InsuranceType::create($data);

                        Notification::make()
                            ->title('Created successfully')
                            ->success()
                            ->send();

                        return $data;
                    })
                    ->tooltip('Add New Insurance Type')
//                    ->visible(fn (): bool => Gate::allows('create', InsurancePlan::class))
                    ->icon('heroicon-o-plus')
                    ->iconButton(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public function render(): View
    {
        return view('livewire.insurance.type.insurance-type-table');
    }
}
