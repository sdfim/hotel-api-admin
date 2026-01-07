<?php

namespace App\Mail;

use App\Services\BookingEmailDataService;
use App\Services\PdfGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\Hotel;

class BookingAgentNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public string $bookingItem;

    public function __construct(string $bookingItem)
    {
        $this->bookingItem = $bookingItem;
    }

    public function build()
    {
        /** @var BookingEmailDataService $dataService */
        $dataService = app(BookingEmailDataService::class);
        $data = $dataService->getBookingData($this->bookingItem);

        // ---- PDF payload ----
        $pdfData = [
            'hotel' => $data['hotel'],
            'hotelData' => [
                'name' => $data['hotelName'],
                'address' => $data['hotelAddress'],
            ],

            // Totals
            'total_net' => $data['total_net'],
            'total_tax' => $data['total_tax'],
            'total_fees' => $data['total_fees'],
            'total_price' => $data['total_price'],
            'currency' => $data['currency'],
            'taxes_and_fees' => $data['taxesAndFees'],
            'advisor_commission' => $data['advisor_commission'],

            // Agency info (can be expanded later if needed)
            'agency' => [
                'booking_agent' => ''.env('APP_NAME').' Tours',
                'booking_agent_email' => 'support@vidanta.com',
            ],

            // Images
            'hotelPhotoPath' => $data['hotelPhotoPath'],

            // Pills / rate info
            'checkin' => $data['checkinDate'],
            'checkout' => $data['checkoutDate'],
            'guest_info' => $data['guestInfo'],
            'main_room_name' => $data['mainRoomName'],
            'rate_refundable' => $data['rateRefundable'],
            'rate_meal_plan' => $data['rateMealPlan'],

            // Perks
            'perks' => $data['perks'],

            // Misc
            // For now we use quoteNumber as confirmation number; replace if real confirmation id appears.
            'confirmation_number' => $data['quoteNumber'],
        ];

        /** @var PdfGeneratorService $pdfService */
        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.advisor-confirmation', $pdfData);

        // ---- Build mail ----
        return $this->subject('Congratulations! Your '.env('APP_NAME').' Booking is Confirmed!')
            ->view('emails.booking.advisor-confirmation')
            ->with([
                // Basic hotel info for the HTML email
                'hotelName' => $data['hotelName'],
            ])
            ->attachData($pdfContent, 'AdvisorConfirmation.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
