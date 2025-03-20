<?php

namespace App\Console\Commands;

use App\Models\HiltonProperty;
use App\Models\Supplier;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\API\Suppliers\HiltonSupplier\HiltonClient;
use Modules\Enums\SupplierNameEnum;
use Modules\Inspector\ExceptionReportController;
use Symfony\Component\Console\Command\Command as CommandAlias;

class FetchHiltonProperties extends Command
{
    protected $signature = 'hilton:fetch-properties {--offset=0} {--limit=50}';

    protected $description = 'Fetch properties from Hilton API and save them to the database';

    private ExceptionReportController $apiExceptionReport;

    private string $report_id;

    private ?int $supplier_id = null;

    public function __construct()
    {
        parent::__construct();
        $this->apiExceptionReport = new ExceptionReportController();
        $this->report_id = Str::uuid()->toString();
    }

    public function handle(): int
    {
        $this->supplier_id = Supplier::where('name', SupplierNameEnum::HILTON->value)->first()?->id;

        if (! $this->supplier_id) {
            $this->error('Supplier ID for Hilton not found. Using default ID 3.');
            $this->supplier_id = 3;
        }

        $this->info("Using Supplier ID: $this->supplier_id");

        $client = new HiltonClient;
        $offset = (int) $this->option('offset');
        $limit = (int) $this->option('limit') ?: 50;
        $totalRecords = $client->getTotalPropertiesCount();

        if ($totalRecords === 0) {
            $this->saveErrorReport('No properties found', 'No data available');

            return CommandAlias::SUCCESS;
        }

        $this->info("Total properties to fetch: $totalRecords, starting from offset: $offset");

        $savedProperties = [];
        $failedProperties = [];

        while ($offset < $totalRecords) {
            $this->info("Fetching properties at offset: $offset");

            $properties = $client->getProperties($offset, $limit);

            if (isset($properties['error'])) {
                $this->saveErrorReport("Failed at offset $offset", json_encode($properties['error']));

                return CommandAlias::FAILURE;
            }

            foreach ($properties as $item) {
                try {
                    $property = HiltonProperty::updateOrCreate(
                        ['prop_code' => $item['propCode']],
                        [
                            'name' => $item['props']['name'] ?? 'Unknown',
                            'facility_chain_name' => $item['props']['facilityChainName'] ?? 'Unknown',
                            'city' => $item['props']['city'] ?? 'Unknown',
                            'country_code' => $item['props']['countryCode'] ?? 'Unknown',
                            'address' => $item['props']['addressLine1'] ?? null,
                            'postal_code' => $item['props']['postalCode'] ?? null,
                            'latitude' => $item['props']['locationDetails']['onlineLatitude'] ?? null,
                            'longitude' => $item['props']['locationDetails']['onlineLongitude'] ?? null,
                            'phone_number' => $item['props']['propDetail']['phoneNumberFull'] ?? null,
                            'email' => $item['props']['propDetail']['email'] ?? null,
                            'website' => $item['props']['propDetail']['website'] ?? null,
                            'star_rating' => $item['props']['propDetail']['ratings']['starRating'] ?? null,
                            'market_tier' => $item['props']['marketTier'] ?? null,
                            'year_built' => $item['props']['propDetail']['yearBuilt'] ?? null,
                            'opening_date' => $item['props']['propDetail']['forecastedOpeningDate'] ?? null,
                            'time_zone' => $item['props']['timeZone'] ?? null, // 🔹 Исправлено
                            'checkin_time' => $item['policy']['registrationPolicy']['checkinTime'] ?? null,
                            'checkout_time' => $item['policy']['registrationPolicy']['checkoutTime'] ?? null,
                            'allow_adults_only' => $item['props']['propDetail']['allowAdultsOnly'] ?? false,

                            'props' => $item['props'] ?? null,
                            'policy' => $item['policy'] ?? null,
                            'taxes' => $item['taxes'] ?? null,
                            'meeting_rooms' => $item['meetingrooms'] ?? null,
                            'safety_and_security' => $item['safetyandsecurity'] ?? null,
                            'location_details' => $item['props']['locationDetails'] ?? null,
                            'area_locators' => $item['props']['locationDetails']['areaLocators'] ?? null,
                            'nearby_corporations' => $item['props']['locationDetails']['nearbyCorporations'] ?? null,
                            'nearby_points' => $item['props']['locationDetails']['pointsofInterest'] ?? null,
                            'guest_room_descriptions' => $item['props']['propDetail']['guestRoomDescriptions'] ?? null,
                        ]
                    );

                    $savedProperties[] = $property->prop_code;
                } catch (Exception $e) {
                    $failedProperties[] = $item['propCode'];
                    $this->error('Error saving property at offset '.$offset.': '.$e->getMessage());
                    $this->saveErrorReport("Error saving at offset $offset", $e->getMessage());
                }
            }

            $offset += $limit;
            sleep(1);
        }

        if (! empty($savedProperties)) {
            if (! empty($failedProperties)) {
                $this->saveSuccessReport(
                    'Successfully fetched and stored properties, but some failed',
                    json_encode(['saved' => count($savedProperties), 'failed' => count($failedProperties)])
                );
                $this->warn(count($failedProperties).' properties failed to save.');
            } else {
                $this->saveSuccessReport(
                    'Successfully fetched and stored all properties',
                    json_encode(['total_saved' => count($savedProperties)])
                );
                $this->info('All properties successfully saved.');
            }
        } else {
            $this->saveErrorReport('Failed to save any properties', 'No data could be stored');

            return CommandAlias::FAILURE;
        }

        return CommandAlias::SUCCESS;
    }

    private function saveSuccessReport(string $description, string $content): void
    {
        $this->saveReport($description, $content, 'success');
    }

    private function saveErrorReport(string $description, string $content): void
    {
        $this->saveReport($description, $content);
    }

    private function saveReport(string $description, string $content, string $level = 'error'): void
    {
        $this->apiExceptionReport->save(
            $this->report_id,
            $level,
            $this->supplier_id,
            'Fetching Hilton Properties',
            $description,
            $content
        );
    }
}
