<?php

namespace App\Console\Commands;

use App\Mail\UnmappedDataReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\HotelContentRepository\Models\Hotel;

class ReportUnmappedDataSupplierRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report-unmapped-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send daily report of unmapped rooms and rates in the supplier repository';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating report of unmapped rooms and rates...');

        $unmappedData = $this->getUnmappedData();

        if (empty($unmappedData)) {
            $this->info('No unmapped data found.');

            return;
        }

        $this->sendReport($unmappedData);

        $this->info('Report has been sent successfully.');
    }

    /**
     * Get all unmapped rooms and rates data
     */
    private function getUnmappedData(): array
    {
        $unmappedData = [];

        // Get all hotels with their rooms and rates
        $hotels = Hotel::with(['rooms.rates', 'rates.rooms'])->get();

        foreach ($hotels as $hotel) {
            $hotelData = [
                'giata_id' => $hotel->giata_code,
                'hotel_name' => $hotel->product->name,
                'unmapped_rooms' => [],
                'unmapped_rates' => [],
            ];

            // Check for unmapped rooms
            foreach ($hotel->rooms as $room) {
                if (is_null($room->external_code)) {
                    $affectedRates = $room->rates->map(function ($rate) {
                        return [
                            'rate_name' => $rate->name,
                            'rate_code' => $rate->code,
                        ];
                    })->values()->toArray();

                    $hotelData['unmapped_rooms'][] = [
                        'room_name' => $room->name,
                        'room_code' => $room->code,
                        'affected_rates' => $affectedRates,
                    ];
                }
            }

            // Check for unmapped rates
            foreach ($hotel->rates as $rate) {
                if (is_null($rate->code)) {
                    $affectedRooms = $rate->rooms->map(function ($room) {
                        return [
                            'room_name' => $room->name,
                            'room_code' => $room->code,
                        ];
                    })->values()->toArray();

                    $hotelData['unmapped_rates'][] = [
                        'rate_name' => $rate->name,
                        'rate_code' => $rate->code,
                        'affected_rooms' => $affectedRooms,
                    ];
                }
            }

            // Only add hotels that have unmapped data
            if (! empty($hotelData['unmapped_rooms']) || ! empty($hotelData['unmapped_rates'])) {
                $unmappedData[] = $hotelData;
            }
        }

        return $unmappedData;
    }

    /**
     * Send the report via email
     */
    private function sendReport(array $unmappedData): void
    {
        $recipientEmail = config('alerts.unmapped_data.email.to');
        $ccEmail = config('alerts.unmapped_data.email.cc');

        if (empty($recipientEmail)) {
            $this->error('No recipient email configured for reports.');

            return;
        }

        $csvPath = storage_path('app/unmapped_data_report_'.now()->format('Ymd_His').'.csv');
        $csv = fopen($csvPath, 'w');
        fwrite($csv, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($csv, ['Type', 'GIATA ID', 'Hotel Name', 'Room/Rate Code', 'Room/Rate Name', 'Affected']);
        foreach ($unmappedData as $hotel) {
            foreach ($hotel['unmapped_rooms'] as $room) {
                $affectedRates = collect($room['affected_rates'])->map(function ($rate) {
                    return $rate['rate_name'].' ('.$rate['rate_code'].')';
                })->implode(', ');
                fputcsv($csv, [
                    'Room',
                    $hotel['giata_id'],
                    $hotel['hotel_name'],
                    $room['room_code'] ?? 'No code',
                    $room['room_name'],
                    $affectedRates ?: 'No rates',
                ]);
            }
            foreach ($hotel['unmapped_rates'] as $rate) {
                $affectedRooms = collect($rate['affected_rooms'])->map(function ($room) {
                    return $room['room_name'].' ('.$room['room_code'].')';
                })->implode(', ');
                fputcsv($csv, [
                    'Rate',
                    $hotel['giata_id'],
                    $hotel['hotel_name'],
                    $rate['rate_code'] ?? 'No code',
                    $rate['rate_name'],
                    $affectedRooms ?: 'No rooms',
                ]);
            }
        }
        fclose($csv);

        try {
            $mail = Mail::to($recipientEmail);

            if (! empty($ccEmail)) {
                $mail->cc($ccEmail);
            }

            $mail->send(new UnmappedDataReport($unmappedData, now()->format('Y-m-d'), $csvPath));

            if (file_exists($csvPath)) {
                unlink($csvPath);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send email: '.$e->getMessage());
            $this->error('Failed to send email: '.$e->getMessage());
        }
    }
}
