<?php

namespace Modules\HotelContentRepository\Livewire\ContactInformation;

use App\Actions\ConfigContactInformationDepartment\CreateConfigContactInformationDepartment;
use App\Actions\ConfigJobDescription\CreateConfigJobDescription;
use App\Helpers\ClassHelper;
use App\Livewire\Configurations\ContactInformationDepartments\ContactInformationDepartmentForm;
use App\Livewire\Configurations\JobDescriptions\JobDescriptionsForm;
use App\Models\Configurations\ConfigContactInformationDepartment;
use App\Models\Configurations\ConfigJobDescription;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Modules\Enums\ContactInformationDepartmentEnum;
use Modules\HotelContentRepository\Actions\ContactInformation\AddContactInformation;
use Modules\HotelContentRepository\Actions\ContactInformation\EditContactInformation;
use Modules\HotelContentRepository\Livewire\Components\CustomRepeater;
use Modules\HotelContentRepository\Models\ContactInformation;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\Vendor;

class ContactInformationTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public int $contactableId;

    public string $contactableType;

    public string $title;

    public function mount(int $contactableId, string $contactableType)
    {
        $this->contactableId = $contactableId;
        $this->contactableType = $contactableType;

        if ($this->contactableType == 'Vendor') {
            $contactable = Vendor::find($contactableId) ?? null;
        } else {
            $contactable = Product::find($contactableId) ?? null;
        }

        $this->title = 'Contact Information for '.($contactable ? $contactable->name : 'Unknown Entity');
    }

    public function schemeForm(): array
    {
        return [
            Hidden::make('contactable_id')
                ->default($this->contactableId)
                ->required(),
            Grid::make(2)
                ->schema([
                    TextInput::make('first_name')
                        ->label('First Name')
                        ->rules(['required', 'string', 'max:191']),
                    TextInput::make('last_name')
                        ->label('Last Name'),
                    TextInput::make('job_title')
                        ->label('Job Title'),
                    Select::make('ujv_departments')
                        ->label('Department')
                        ->required()
                        ->multiple()
                        ->options(ConfigJobDescription::pluck('name', 'id'))
                        ->createOptionForm(Gate::allows('create', ConfigJobDescription::class) ? JobDescriptionsForm::getSchema() : [])
                        ->createOptionUsing(function (array $data) {
                            /** @var CreateConfigJobDescription $actionDescription */
                            $actionDescription = app(CreateConfigJobDescription::class);
                            $description = $actionDescription->create($data);
                            Notification::make()
                                ->title('Department created successfully')
                                ->success()
                                ->send();

                            return $description->id;
                        }),
                ]),

            CustomRepeater::make('phones')
                ->label('Phones')
                ->defaultItems(0)
                ->schema([
                    Grid::make(6)
                        ->schema([
                            TextInput::make('country_code')
                                ->hiddenLabel()
                                ->placeholder('Country Code*')
                                ->rules(['required', 'string', 'max:5']),
                            TextInput::make('area_code')
                                ->hiddenLabel()
                                ->placeholder('Area Code'),
                            TextInput::make('phone')
                                ->hiddenLabel()
                                ->placeholder('Phone*')
                                ->rules(['required', 'string', 'max:15']),
                            TextInput::make('extension')
                                ->hiddenLabel()
                                ->placeholder('Extension'),
                            Textarea::make('description')
                                ->hiddenLabel()
                                ->placeholder('Description')
                                ->columnSpan(2),
                        ]),
                ]),

            CustomRepeater::make('emails')
                ->label('Emails')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('email')
                                ->hiddenLabel()
                                ->rules(['required'])
                                ->placeholder('Email*'),
                            Select::make('departments')
                                ->hiddenLabel()
                                ->placeholder('Select UJV Department')
                                ->searchable()
                                ->native(false)
                                ->multiple()
                                ->options(ConfigContactInformationDepartment::pluck('name', 'name'))
                                ->createOptionForm(Gate::allows('create', ConfigContactInformationDepartment::class) ? ContactInformationDepartmentForm::getSchema() : [])
                                ->createOptionUsing(function (array $data) {
                                    /** @var CreateConfigContactInformationDepartment $action */
                                    $action = app(CreateConfigContactInformationDepartment::class);
                                    $department = $action->create($data);
                                    Notification::make()
                                        ->title('Department created successfully')
                                        ->success()
                                        ->send();

                                    return $department->name;
                                })
                                ->columnSpan(2),
                        ]),
                ]),
        ];
    }

    protected function createEmailColumn($name)
    {
        return TextColumn::make($name)
            ->label($name)
            ->getStateUsing(function ($record) use ($name) {
                return $record->emails->filter(function ($email) use ($name) {
                    return in_array($name, $email['departments']);
                })->pluck('email')->implode('<br>');
            })
            ->html()
            ->wrap();
    }

    public function table(Table $table): Table
    {
        $categories = ConfigContactInformationDepartment::pluck('name')->toArray();

        $emailColumns = array_map(function ($category) {
            return $this->createEmailColumn($category);
        }, $categories);

        $columns = [
            TextColumn::make('first_name')->label('First Name')->wrap(),
            TextColumn::make('last_name')->label('Last Name'),
            TextColumn::make('job_title')->label('Job Title')->wrap(),
            TextColumn::make('job_title')
                ->label('Job Title/Departments')
                ->getStateUsing(function ($record) {
                    $ujvDepartments = $record->ujvDepartments ?? collect();

                    return "{$record->job_title} / {$ujvDepartments->pluck('name')->implode(', ')}";
                })
                ->wrap(),
            TextColumn::make('phones')
                ->label('Phones')
                ->extraAttributes(['style' => 'min-width: 150px;'])
                ->getStateUsing(function ($record) {
                    return $record->phones->map(function ($phone) {
                        return "{$phone['country_code']} {$phone['area_code']} {$phone['phone']} {$phone['extension']}";
                    })->implode('<br>');
                })
                ->html()
                ->wrap(),
        ];

        $columns = array_merge($columns, $emailColumns);
        $columns[] = TextColumn::make('Uncategorized')
            ->label('Uncategorized')
            ->getStateUsing(function ($record) use ($categories) {
                $categorizedEmails = $record->emails->filter(function ($email) use ($categories) {
                    foreach ($categories as $category) {
                        if (in_array($category, $email['departments'])) {
                            return true;
                        }
                    }

                    return false;
                });

                return $record->emails->diff($categorizedEmails)->pluck('email')->implode('<br>');
            })
            ->html()
            ->wrap();

        return $table
            ->query(
                ContactInformation::with('emails', 'phones')
                    ->where('contactable_id', $this->contactableId)
                    ->where('contactable_type', 'Modules\\HotelContentRepository\\Models\\'.$this->contactableType)
            )
            ->columns($columns)
            ->actions([
                EditAction::make()
                    ->label('')
                    ->modalWidth('6xl')
                    ->modalHeading(new HtmlString("Edit {$this->title}"))
                    ->tooltip('Edit Contact Information')
                    ->form($this->schemeForm())
                    ->closeModalByClickingAway(false)
                    ->fillForm(function ($record) {
                        $data = $record->toArray();
                        $data['ujv_departments'] = $record->ujvDepartments->pluck('id')->toArray();
                        $data['emails'] = $record->emails->toArray();
                        $data['phones'] = $record->phones->toArray();

                        return $data;
                    })
                    ->action(function ($data, $record) {
                        /** @var EditContactInformation $editContactInformation */
                        $editContactInformation = app(EditContactInformation::class);
                        $editContactInformation->execute($data, $record, $this->contactableType);
                    })
                    ->visible(Gate::allows('create', Hotel::class)),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->visible(Gate::allows('create', Hotel::class)),
            ])
            ->headerActions([
                CreateAction::make('addContact')
                    ->modalHeading(new HtmlString("Create {$this->title}"))
                    ->modalWidth('6xl')
                    ->form($this->schemeForm())
                    ->closeModalByClickingAway(false)
                    ->action(function ($data, CreateAction $action) {
                        /** @var AddContactInformation $addContactInformation */
                        $addContactInformation = app(AddContactInformation::class);
                        $addContactInformation->execute($data, $this->contactableId, $this->contactableType);
                        if (Arr::get($action->getArguments(), 'another', false)) {
                            Notification::make()
                                ->title('Contact Information created successfully')
                                ->success()
                                ->send();
                            $this->reset('mountedTableActionsData');
                            $this->mountedTableActionsData[0]['contactable_id'] = $this->contactableId;
                            $this->mountedTableActionsData[0]['emails'][] = ['email' => '', 'departments' => []];
                            $action->halt();
                        }
                    })
                    ->tooltip('Add New Contact Information')
                    ->icon('heroicon-o-plus')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(Gate::allows('create', Hotel::class)),
                CreateAction::make('copyEmails')
                    ->label('Copy Emails')
                    ->modalWidth('sm')
                    ->createAnother(false)
                    ->modalHeading('Copy Emails to Clipboard')
                    ->form([
                        Select::make('department')
                            ->label('Department')
                            ->options(ContactInformationDepartmentEnum::options())
                            ->required(),
                    ])
                    ->action(function ($data, Component $livewire) {
                        $livewire->js('console.log("action clicked")');
                        $emails = ContactInformation::where('contactable_id', $this->contactableId)
                            ->with(['emails' => function ($query) use ($data) {
                                $query->where('departments', 'like', '%'.$data['department'].'%');
                            }])->get()->pluck('emails.*.email')->flatten()->toArray();
                        $emailsString = implode('; ', $emails);
                        $livewire->dispatch('copy-to-clipboard', ['emails' => $emailsString]);
                        Notification::make()
                            ->title('Emails сopied to clipboard')
                            ->body($emailsString)
                            ->success()
                            ->send();
                    })
                    ->tooltip('Copy Emails to Clipboard')
                    ->icon('heroicon-o-clipboard')
                    ->extraAttributes(['class' => ClassHelper::buttonClasses()])
                    ->iconButton()
                    ->visible(Gate::allows('create', Hotel::class)),
            ]);
    }

    public function render()
    {
        return view('livewire.products.contact-information-table');
    }
}
