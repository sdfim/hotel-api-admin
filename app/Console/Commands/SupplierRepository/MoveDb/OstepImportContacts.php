<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use App\Models\Configurations\ConfigJobDescription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\ContactInformation;
use Modules\HotelContentRepository\Models\ContactInformationEmails;
use Modules\HotelContentRepository\Models\ContactInformationPhones;
use Modules\HotelContentRepository\Models\Hotel;

class OstepImportContacts extends Command
{
    protected $signature = 'move-db:import-contacts';

    protected $description = 'Import contacts from donor database tables hotel_contacts, hotel_contact_emails, and hotel_contact_phones';

    public function handle()
    {
        $this->warn('-> O step Import Contacts');

        $donorContacts = DB::connection('donor')->table('hotel_contacts')
            ->select('hotel_contacts.*')
            ->get();

        $this->newLine();

        $this->withProgressBar($donorContacts, function ($donorContact) {
            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($donorContact) {
                $query->where('crm_hotel_id', $donorContact->hotel_id);
            })->first();

            if (! $hotel || ! $hotel->product) {
                return;
            }

            $this->output->write("\033[1A\r\033[KImporting contact for hotel {$hotel->product->name} ({$hotel->id} | {$hotel->crmMapping->crm_hotel_id})\n");

            $contactInformation = ContactInformation::updateOrCreate(
                [
                    'first_name' => $donorContact->name,
                    'job_title' => $donorContact->title,
                    'contactable_id' => $hotel->id,
                    'contactable_type' => 'Modules\HotelContentRepository\Models\Product',
                ]
            );

            $categories = [];
            $categorizedRecords = $this->categorizeRecords([$donorContact->title], $categories);

            $departmentIds = ConfigJobDescription::whereIn('name', $categorizedRecords[$donorContact->title])->pluck('id')->toArray();

            $contactInformation->ujvDepartments()->sync($departmentIds);

            $emails = DB::connection('donor')->table('hotel_contact_emails')
                ->where('contact_id', $donorContact->id)
                ->get()
                ->map(function ($email) use ($contactInformation) {
                    return ContactInformationEmails::updateOrCreate(
                        [
                            'contact_information_id' => $contactInformation->id,
                            'email' => $email->email,
                        ],
                        [
                            'departments' => array_values(array_filter([
                                $email->is_accounting ? 'Accounting' : null,
                                $email->is_reservation ? 'Reservation' : null,
                                $email->is_vip ? 'VIP 7 Day' : null,
                                $email->is_vip_su ? 'VIP SU' : null,
                                $email->is_sales_marketing ? 'Sales Marketing' : null,
                                $email->is_concierge ? 'Concierge' : null,
                            ])),
                        ]
                    );
                });

            $phones = DB::connection('donor')->table('hotel_contact_phones')
                ->where('contact_id', $donorContact->id)
                ->get()
                ->map(function ($phone) use ($contactInformation) {
                    return ContactInformationPhones::updateOrCreate(
                        [
                            'contact_information_id' => $contactInformation->id,
                            'phone' => $phone->phone,
                        ],
                        [
                            'country_code' => $phone->country_code,
                            'area_code' => $phone->area_code,
                            'extension' => $phone->extension,
                            'description' => $phone->description,
                        ]
                    );
                });

            $contactInformation->emails()->saveMany($emails);
            $contactInformation->phones()->saveMany($phones);
        });

        $this->info("\nContacts imported successfully.");
    }

    public function categorizeRecords(array $records, array &$categories): array
    {
        $keywords = [
            'VIP 7 Day' => ['vip 7 day'],
            'VIP SU' => ['vip su'],
            'PD Contact' => ['pd contact'],
            'Reservation' => ['reservation', 'reservas', 'book', 'res &', 'direct reservation'],
            'Sales Marketing' => ['sales', 'marketing', 'director of sales', 'sales manager', 'revenue'],
            'Concierge' => ['concierge', 'guest services'],
            'Direct Connect' => ['direct connect', 'connectivity'],
            'Accounting' => ['accounting', 'finance', 'accounts'],
        ];

        $categorized = [];

        foreach ($records as $record) {
            $matchedCategories = [];
            $lowerRecord = strtolower($record);

            foreach ($keywords as $category => $terms) {
                foreach ($terms as $term) {
                    if (strpos($lowerRecord, $term) !== false) {
                        $matchedCategories[] = $category;
                        break;
                    }
                }
            }

            if (empty($matchedCategories)) {
                $newCategory = 'Uncategorized: '.$record;
                $categories[$newCategory] = [];
                $matchedCategories[] = $newCategory;
            }

            $categorized[$record] = $matchedCategories;
        }

        return $categorized;
    }
}
