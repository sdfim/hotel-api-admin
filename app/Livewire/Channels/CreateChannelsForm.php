<?php

namespace App\Livewire\Channels;

use App\Models\Channel;
use App\Models\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

class CreateChannelsForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->unique()
                    ->required()
                    ->maxLength(191),

                TextInput::make('description')
                    ->required()
                    ->maxLength(191),

                // Multi-select of API users (free only: channel_id IS NULL)
                Select::make('user_ids')
                    ->label('API Users')
                    ->multiple()
                    ->searchable()
                    // Lightweight initial options page (api-user & free)
                    ->options(fn () => User::query()
                        ->whereHas('roles', fn ($q) => $q->where('slug', RoleSlug::API_USER->value))
                        ->whereNull('channel_id')
                        ->orderBy('name')
                        ->limit(20)
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    // Async search limited to api-user & free
                    ->getSearchResultsUsing(fn (string $search) => User::query()
                        ->whereHas('roles', fn ($q) => $q->where('slug', RoleSlug::API_USER->value))
                        ->whereNull('channel_id')
                        ->when($search !== '', function ($q) use ($search) {
                            $q->where(function ($qq) use ($search) {
                                $qq->where('name', 'like', "%$search%")
                                    ->orWhere('email', 'like', "%$search%");
                            });
                            if (is_numeric($search)) {
                                $q->orWhere('id', (int) $search);
                            }
                        })
                        ->orderBy('name')
                        ->limit(20)
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    // Resolve labels for already selected values
                    ->getOptionLabelsUsing(fn (array $values): array => User::query()->whereIn('id', $values)->pluck('name', 'id')->toArray()
                    )
                    // Create user in a modal -> give api-user role (channel will be set after saving channel)
                    ->createOptionForm([
                        TextInput::make('name')->required()->maxLength(191),
                        TextInput::make('email')->email()->required()->unique(User::class),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required()
                            ->default(fn () => Str::password(10)),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        $user = User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => bcrypt($data['password']),
                        ]);

                        // Ensure api-user role
                        $apiRoleId = Role::where('slug', RoleSlug::API_USER->value)->value('id');
                        if ($apiRoleId) {
                            $user->roles()->syncWithoutDetaching([$apiRoleId]);
                        }

                        Notification::make()
                            ->title('API user successfully created')
                            ->success()
                            ->send();

                        return $user->id; // returned ID becomes selected
                    }),
            ])
            ->statePath('data')
            ->model(Channel::class);
    }

    public function create(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        // Create channel
        $channel = Channel::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'user_id' => auth()->id(),
        ]);

        // Issue token
        $token = $channel->createToken($data['name']);
        $channel->update([
            'token_id' => $token->accessToken->id,
            'access_token' => $token->plainTextToken,
        ]);

        // Bind selected users safely: only those still free (channel_id IS NULL)
        $selectedIds = $data['user_ids'] ?? [];
        if (! empty($selectedIds)) {
            $attachableIds = User::query()
                ->whereIn('id', $selectedIds)
                ->whereNull('channel_id')
                ->pluck('id')
                ->all();

            if (! empty($attachableIds)) {
                User::whereIn('id', $attachableIds)->update(['channel_id' => $channel->id]);

                $apiRoleId = Role::where('slug', RoleSlug::API_USER->value)->value('id');
                if ($apiRoleId) {
                    User::whereIn('id', $attachableIds)
                        ->get()
                        ->each(fn (User $u) => $u->roles()->syncWithoutDetaching([$apiRoleId]));
                }
            }

            // Warn if some were skipped because already attached elsewhere
            $skipped = array_diff($selectedIds, $attachableIds);
            if (! empty($skipped)) {
                Notification::make()
                    ->title('Some users were skipped because they are already attached to another channel.')
                    ->warning()
                    ->send();
            }
        }

        Notification::make()
            ->title('Created successfully')
            ->success()
            ->send();

        return redirect()->route('channels.index');
    }

    public function render(): View
    {
        return view('livewire.channels.create-channels-form');
    }
}
