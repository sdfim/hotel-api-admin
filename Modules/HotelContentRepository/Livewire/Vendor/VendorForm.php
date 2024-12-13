<?php

namespace Modules\HotelContentRepository\Livewire\Vendor;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Actions\Jetstream\UpdateTeamName;
use App\Livewire\Users\UsersForm;
use App\Models\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\HotelContentRepository\Models\Vendor;

class VendorForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public Vendor $record;
    public bool $verified;
    public $showDeleteConfirmation = false;

    public function mount(?Vendor $vendor): void
    {
        $this->record = $vendor ?? new Vendor();

        $this->verified = $vendor->verified ?? false;

        $attributes = $this->record->attributesToArray();

        if ($this->record->exists && $this->record->team) {
            $userIds = $this->record->team->allUsers()->pluck('id')->toArray();
            $attributes = array_merge($attributes, ['user_ids' => $userIds]);
        }

        $this->form->fill($attributes);
    }

    public function toggleVerified()
    {
        $this->verified = !$this->verified;
        $this->record->update(['verified' => $this->verified]);
    }

    public function confirmDeleteVendor()
    {
        $this->showDeleteConfirmation = true;
    }

    public function deleteVendor()
    {
        \DB::transaction(function () {
            foreach ($this->record->products as $product) {
                $product->related->delete();
                $product->delete();
            }
            $this->record->delete();
        });

        Notification::make()
            ->title('Vendor deleted successfully')
            ->success()
            ->send();

        $this->showDeleteConfirmation = false;

        return redirect()->route('vendor-repository.index');
    }

    public function form(Form $form): Form
    {
        $mapComponent = null;
        if (config('filament-google-maps.key')) {
            $mapComponent = Map::make('location')
                ->label('')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $this->handleReverseGeocoding($state, $set);
                })
                ->height(fn () => '300px')
                ->defaultZoom(17)
                ->autocomplete('full_address')
                ->autocompleteReverse(true)
                ->reverseGeocode([
                    'street' => '%n %S',
                    'city' => '%L',
                    'state' => '%A1',
                    'zip' => '%z',
                ])
                ->defaultLocation(fn () => [$this->data['lat'] ?? 39.526610, $this->data['lng'] ?? -107.727261])
                ->draggable()
                ->clickable(false)
                ->geolocate()
                ->geolocateLabel('Get Location')
                ->geolocateOnLoad(true, false)
                ->columnSpan(1);
        }

        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')->label('Name')->required(),
                        Select::make('user_ids')
                            ->label('Users')
                            ->getSearchResultsUsing(
                                fn (string $search): array => User::where('email', 'like', "%$search%")
                                    ->limit(10)->pluck('email', 'id')->toArray()
                            )
                            ->getOptionLabelsUsing(
                                fn (array $values): ?array => User::whereIn('id', $values)
                                    ->pluck('email', 'id')->toArray()
                            )
                            ->multiple()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(191),
                                TextInput::make('email')
                                    ->unique(ignorable: $this->record)
                                    ->required()
                                    ->email()
                                    ->maxLength(191),
                                TextInput::make('password')
                                    ->required()
                                    ->password()
                                    ->revealable()
                                    ->formatStateUsing(fn () => Str::password(10)),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $user = User::create([
                                    'name' => $data['name'],
                                    'email' => $data['email'],
                                    'password' => bcrypt($data['password']),
                                ]);
                                $user->roles()->sync(Role::where('slug', RoleSlug::EXTERNAL_USER)->firstOrFail());
                                Notification::make()
                                    ->title('User created successfully')
                                    ->success()
                                    ->send();
                                return $user->id;
                            }),
                ]),

                Grid::make(2)
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('full_address')
                                    ->label('Get Location by Address')
                                    ->required()
                                    ->placeholder(fn($get) => $get('address')),
                                TextInput::make('lat')->label('Latitude')->numeric()->readOnly(),
                                TextInput::make('lng')->label('Longitude')->numeric()->readOnly(),
                            ])->columnSpan(1),

                        $mapComponent ?? Placeholder::make('map_message')
                            ->label('Google Map')
                            ->content('Please add GOOGLE_API_DEVELOPER_KEY to the .env file to display the Google Map and search coordinates by address.'),
                    ]),

                Grid::make(1)
                    ->schema([
                        TextInput::make('address')->label('Address')->readOnly(),
                    ]),

                Grid::make(2)
                    ->schema([
                    TextInput::make('website')->label('Website'),
                    Select::make('galleries')
                        ->label('Galleries')
                        ->multiple()
                        ->relationship('galleries', 'gallery_name')
                        ->preload(),
                ]),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function edit(): Redirector|RedirectResponse
    {
        $this->validate();
        $this->record->fill($this->data);
        $this->record->verified = $this->verified ?? false;
        $isNew = !$this->record->exists || !$this->record->team;
        $this->record->save();

        $userIds = $this->data['user_ids'];
        if ($isNew) {
            $this->createTeam($userIds);
            $message = 'Vendor created successfully';
        } else {
            $this->updateTeam($userIds);
            $message = 'Vendor updated successfully';
        }

        if (isset($this->data['galleries'])) {
            $this->record->galleries()->sync($this->data['galleries']);
        }

        Notification::make()
            ->title($message)
            ->success()
            ->send();

        session()->flash('message', $message);
        return redirect()->route('vendor-repository.index');
    }

    protected function handleReverseGeocoding(array $state, callable $set): void
    {
        if (isset($state['lat']) && isset($state['lng'])) {
            $set('lat', $state['lat']);
            $set('lng', $state['lng']);

            // Reverse geocoding logic
            $apiKey = config('filament-google-maps.key');
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$state['lat']},{$state['lng']}&key={$apiKey}";

            $response = file_get_contents($url);
            $results = json_decode($response, true);
            $streetNumber = $route = $city = $postal_town = $state_province_name = $zip = $country_code = '';

            if (!empty($results['results'][0]['address_components'])) {
                $components = $results['results'][0]['address_components'];

                // Populate address fields
                foreach ($components as $component) {
                    if (in_array('street_number', $component['types'])) {
                        $streetNumber = $component['long_name'];
                    }
                    if (in_array('route', $component['types'])) {
                        $route = $component['long_name'];
                    }
                    if (in_array('locality', $component['types'])) {
                        $city = $component['long_name'];
                    }
                    if (in_array('postal_town', $component['types'])) {
                        $postal_town = $component['long_name'];
                    }
                    if (in_array('administrative_area_level_1', $component['types'])) {
                        $state_province_name = $component['long_name'];
                    }
                    if (in_array('postal_code', $component['types'])) {
                        $zip = $component['long_name'];
                    }
                    if (in_array('country', $component['types'])) {
                        $country_code = $component['short_name'];
                    }
                }

                $set('address', trim("$streetNumber $route, $zip"));
            }
        }
    }

    public function createTeam(array $userIds = []): void
    {
        $team = null;
        $owner = null;
        foreach ($userIds as $index => $userId) {
            $user = User::find($userId);
            if ($index === 0) {
                // Set the first user as the owner and create the team
                $owner = $user;
                $team = resolve(CreateTeam::class)->create($owner, ['name' => $this->record->name]);
            } else {
                // Add remaining users to the team
                resolve(AddTeamMember::class)->add($owner, $team, $user->email, 'admin');
            }
            // Switch all users to the current team
            $user->switchTeam($team);
        }

        if ($team) $team->update(['vendor_id' => $this->record->id]);

        Notification::make()
            ->title('Team created successfully')
            ->success()
            ->send();
    }

    public function updateTeam(array $userIds = []): void
    {
        $owner = User::findOrFail(array_shift($userIds));
        $team = $this->record->team;
        if ($team->owner->id != $owner->id) {
            $team->users()->detach($owner);
            $team->owner()->associate($owner);
        }
        $teamUsers = $team->users->pluck('id')->toArray();

        foreach ($userIds as $userId) {
            if (in_array($userId, $teamUsers)) continue;

            $user = User::findOrFail($userId);
            resolve(AddTeamMember::class)->add($owner, $team, $user->email, 'admin');
            $user->switchTeam($team);
        }

        foreach ($teamUsers as $teamUser) {
            if (!in_array($teamUser, $userIds)) {
                $user = User::findOrFail($teamUser);
                resolve(RemoveTeamMember::class)->remove($owner, $team, $user);
            }
        }

        resolve(UpdateTeamName::class)->update(
            $owner, $team, ['name' => $this->record->name],
        );

        Notification::make()
            ->title('Team updated successfully')
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.vendors.vendor-form');
    }
}
