<?php

namespace App\Livewire\Channels;

use App\Models\Channels;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class CreateChannelsForm extends Component implements HasForms
{
    use InteractsWithForms;

    /**
     * @var array|null
     */
    public ?array $data = [];

    /**
     * @return void
     */
    public function mount(): void
    {
        $this->form->fill();
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(191),
                TextInput::make('description')
                    ->required()
                    ->maxLength(191),
            ])
            ->statePath('data')
            ->model(Channels::class);
    }

    /**
     * @return Redirector|RedirectResponse
     */
    public function create(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();
        $token = auth()->user()->createToken($data['name']);
        $data['token_id'] = $token->accessToken->id;
        $data['access_token'] = $token->plainTextToken;
        $record = Channels::create($data);

        $this->form->model($record)->saveRelationships();

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('channels.index');
    }

    /**
     * @return View
     */
    public function render(): View
    {
        return view('livewire.channels.create-channels-form');
    }
}
