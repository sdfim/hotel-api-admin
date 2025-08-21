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

class UpdateChannelsForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public Channel $record;

    public function mount(Channel $channel): void
    {
        $this->record = $channel;

        // Pre-fill currently attached API users
        $this->form->fill([
            ...$this->record->attributesToArray(),
            'user_ids' => $this->record->users()
                ->whereHas('roles', fn ($q) => $q->where('slug', RoleSlug::API_USER->value))
                ->pluck('id')
                ->toArray(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->unique(ignorable: $this->record)
                    ->required()
                    ->maxLength(191),

                TextInput::make('description')
                    ->required()
                    ->maxLength(191),

                // Multi-select of API users (free OR already attached to this channel)
                Select::make('user_ids')
                    ->label('API Users')
                    ->multiple()
                    ->searchable()
                    // Initial options page: api-user & (free OR this channel)
                    ->options(fn () => User::query()
                        ->whereHas('roles', fn ($q) => $q->where('slug', RoleSlug::API_USER->value))
                        ->where(function ($q) {
                            $q->whereNull('channel_id')
                                ->orWhere('channel_id', $this->record->id);
                        })
                        ->orderBy('name')
                        ->limit(20)
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    // Async search: api-user & (free OR this channel)
                    ->getSearchResultsUsing(fn (string $search) => User::query()
                        ->whereHas('roles', fn ($q) => $q->where('slug', RoleSlug::API_USER->value))
                        ->where(function ($q) {
                            $q->whereNull('channel_id')
                                ->orWhere('channel_id', $this->record->id);
                        })
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
                    // Resolve labels for selected values
                    ->getOptionLabelsUsing(fn (array $values): array => User::query()->whereIn('id', $values)->pluck('name', 'id')->toArray()
                    )
                    // Create user in modal -> immediately bind to this channel
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
                            'channel_id' => $this->record->id,
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

                        return $user->id;
                    }),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $data = $this->form->getState();

        // Update channel fields
        $this->record->update([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);

        // Compute diffs between current and selected users
        $selectedIds = $data['user_ids'] ?? [];
        $currentIds = $this->record->users()->pluck('id')->toArray();

        $toAttach = array_values(array_diff($selectedIds, $currentIds));
        $toDetach = array_values(array_diff($currentIds, $selectedIds));

        // Attach only users that are still free (channel_id IS NULL)
        if (! empty($toAttach)) {
            $attachableIds = User::query()
                ->whereIn('id', $toAttach)
                ->whereNull('channel_id')
                ->pluck('id')
                ->all();

            if (! empty($attachableIds)) {
                User::whereIn('id', $attachableIds)->update(['channel_id' => $this->record->id]);

                $apiRoleId = Role::where('slug', RoleSlug::API_USER->value)->value('id');
                if ($apiRoleId) {
                    User::whereIn('id', $attachableIds)
                        ->get()
                        ->each(fn (User $u) => $u->roles()->syncWithoutDetaching([$apiRoleId]));
                }
            }

            $skipped = array_diff($toAttach, $attachableIds);
            if (! empty($skipped)) {
                Notification::make()
                    ->title('Some users were skipped because they are already attached to another channel.')
                    ->warning()
                    ->send();
            }
        }

        // Detach: clear channel_id (keep role as is)
        if (! empty($toDetach)) {
            User::whereIn('id', $toDetach)->update(['channel_id' => null]);
        }

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        return redirect()->route('channels.index');
    }

    public function render(): View
    {
        return view('livewire.channels.update-channels-form');
    }
}
