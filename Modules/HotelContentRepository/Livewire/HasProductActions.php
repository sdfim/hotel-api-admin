<?php

namespace Modules\HotelContentRepository\Livewire;

use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use App\Helpers\ClassHelper;
use Modules\HotelContentRepository\Models\Product;

trait HasProductActions
{
    public static bool $shouldUseSaveData = false;

    protected function getActions(): array
    {
        $actions = [
            EditAction::make('Save Changes')
                ->iconButton()
                ->modalHeading(new HtmlString("Edit {$this->title}"))
                ->form($this->schemeForm())
                ->visible(fn () => Gate::allows('create', Product::class)),
        ];

        if (self::$shouldUseSaveData) {
            $actions[0]->action('saveData');
        }

        return $actions;
    }

    protected function getBulkActions(): array
    {
        return [
            DeleteBulkAction::make()
                ->visible(fn () => Gate::allows('create', Product::class)),
        ];
    }

    protected function getHeaderActions(): array
    {
        $actions = [
            CreateAction::make()
                ->modalHeading(new HtmlString("Create {$this->title}"))
                ->form($this->schemeForm())
                ->tooltip('Add New Fee')
                ->icon('heroicon-o-plus')
                ->visible(fn () => Gate::allows('create', Product::class))
                ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                ->iconButton(),
        ];

        if (self::$shouldUseSaveData) {
            $actions[0]->action('saveData');
        }

        return $actions;
    }
}
