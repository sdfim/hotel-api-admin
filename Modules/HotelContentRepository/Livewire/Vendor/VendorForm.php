<?php

namespace Modules\HotelContentRepository\Livewire\Vendor;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Actions\Jetstream\UpdateTeamName;
use App\Helpers\Strings;
use App\Models\Enums\RoleSlug;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Modules\Enums\VendorTypeEnum;
use Modules\HotelContentRepository\Livewire\Components\CustomToggle;
use Modules\HotelContentRepository\Livewire\Hotel\HotelTable;
use Modules\HotelContentRepository\Models\Hotel;
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
        $this->record = $vendor ?? app(Vendor::class);

        $this->verified = $vendor->verified ?? true;

        $vendor = $this->record->attributesToArray();

        if ($this->record->address && is_array($this->record->address)) {
            foreach ($this->record->address as $key => $value) {
                $vendor['addressArr'][$key] = $value;
            }
        } else {
            $vendor['addressArr'] = [];
        }

        if ($this->record->exists && $this->record->team) {
            $userIds = $this->record->team->allUsers()->pluck('id')->toArray();
            $vendor = array_merge($vendor, ['user_ids' => $userIds]);
        }

        $vendor['independent_flag'] = $this->record->independent_flag ?? false;
        $vendor['giata_code_visible'] = $this->record->independent_flag && $this->record->products->count() < 1 ?? false;

        $vendor['giata_code'] = $this->record->products->first()->related->giata_code ?? null;

        $this->form->fill($vendor);
    }

    public function toggleActivated()
    {
        $this->verified = ! $this->verified;
        $this->record->update(['verified' => $this->verified]);

        if (! $this->verified) {
            $this->record->products()->update([
                'OnSale' => false,
                'on_sale_causation' => 'Vendor Activation status changed to false',
            ]);

            Notification::make()
                ->title('Products Updated')
                ->body('All products have been set to not on sale due to vendor deactivation.')
                ->danger()
                ->send();
        } else {
            Notification::make()
                ->title('Vendor Activated')
                ->body('The vendor has been successfully activated.')
                ->success()
                ->send();
        }
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
                TextInput::make('name')->label('Name')->required(),
                Grid::make(2)
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->multiple()
                            ->options(VendorTypeEnum::getOptions())
                            ->required(),
                        Select::make('user_ids')
                            ->label('Users')
                            ->native(false)
                            ->options(
                                User::pluck('email', 'id')->toArray()
                            )
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
                                    ->unique(User::class, 'email')
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
                                    ->placeholder(fn ($get) => $get('addressArr.line_1').' '.$get('addressArr.city')),
                                TextInput::make('lat')->label('Latitude')->numeric()->readOnly(),
                                TextInput::make('lng')->label('Longitude')->numeric()->readOnly(),
                            ])->columnSpan(1),

                        $mapComponent ?? Placeholder::make('map_message')
                            ->label('Google Map')
                            ->content('Please add GOOGLE_API_DEVELOPER_KEY to the .env file to display the Google Map and search coordinates by address.'),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('addressArr.city')
                            ->label('City'),
                        TextInput::make('addressArr.line_1')
                            ->label('Line 1'),
                        TextInput::make('addressArr.country_code')
                            ->label('Country Code'),
                        TextInput::make('addressArr.state_province_name')
                            ->label('State Province Name'),
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

                Grid::make(6)
                    ->schema([
                        CustomToggle::make('independent_flag')
                            ->label('Independent')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('giata_code_visible', true);
                                } else {
                                    $set('giata_code_visible', false);
                                }
                            })
                            ->disabled(function () {
                                return ($this->record->exists && $this->record->products->count() > 0) || ! in_array(VendorTypeEnum::HOTEL->value, $this->record->type);
                            }),
                        Select::make('giata_code')
                            ->label('GIATA code')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): ?array {
                                $preparedSearchText = Strings::prepareSearchForBooleanMode($search);
                                $result = Property::select(
                                    DB::raw('CONCAT(name, " (", city, ", ", locale, ")") AS full_name, code'))
                                    ->whereRaw("MATCH(search_index) AGAINST('$preparedSearchText' IN BOOLEAN MODE)")
                                    ->limit(100);

                                return $result->pluck('full_name', 'code')
                                    ->mapWithKeys(function ($full_name, $code) {
                                        return [$code => $code.' ('.$full_name.')'];
                                    })
                                    ->toArray() ?? [];
                            })
                            ->getOptionLabelUsing(function (string $value): ?string {
                                $properties = Property::select(DB::raw('CONCAT(code, " (", name, ", location: ", city, ", ", locale, ")") AS full_name'), 'code')
                                    ->where('code', $value)
                                    ->first()
                                    ->full_name ?? '';

                                return $properties;
                            })
                            ->columnSpan(2)
                            ->visible(fn ($get) => $get('giata_code_visible'))
                            ->hidden(fn ($get) => ! $get('giata_code_visible')),
                    ]),

            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function save(): Redirector|RedirectResponse
    {
        $this->validate();
        $isCreate = ! $this->record->exists;

        $this->data['address'] = $this->data['addressArr'];

        $this->record->fill($this->data);
        $this->record->verified = $this->verified ?? true;
        $isNew = ! $this->record->exists || ! $this->record->team;
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

        if ($this->data['independent_flag']) {
            $existHotels = Hotel::whereHas('product', function ($query) {
                $query->where('vendor_id', $this->record->id);
            })->get();
            if ($isCreate || $existHotels->isEmpty() || $existHotels->count() == 1) {
                $dataGiata['giata_code'] = $this->data['giata_code'];
                $dataGiata['product']['vendor_id'] = $this->record->id;
                resolve(HotelTable::class)->saveHotelWithGiataCode($dataGiata);
            } else {
                foreach ($existHotels as $hotel) {
                    $hotel->product->name = $this->data['name'];
                    $hotel->product->lat = $this->data['lat'];
                    $hotel->product->lng = $this->data['lng'];
                    $hotel->product->website = $this->data['website'];
                    $hotel->address = $this->data['addressArr'];
                    $hotel->save();
                    $hotel->product->save();
                }
            }
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

            if (! empty($results['results'][0]['address_components'])) {
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

                //                $set('address', trim("$streetNumber $route, $zip"));
                $set('addressArr.line_1', trim("$streetNumber $route, $zip"));
                $set('addressArr.city', $city !== '' ? $city : $postal_town);
                $set('addressArr.state_province_name', $state_province_name);
                $set('addressArr.country_code', $country_code);
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

        if ($team) {
            $team->update(['vendor_id' => $this->record->id]);
        }

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
            if (in_array($userId, $teamUsers)) {
                continue;
            }

            $user = User::findOrFail($userId);
            resolve(AddTeamMember::class)->add($owner, $team, $user->email, 'admin');
            $user->switchTeam($team);
        }

        foreach ($teamUsers as $teamUser) {
            if (! in_array($teamUser, $userIds)) {
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
