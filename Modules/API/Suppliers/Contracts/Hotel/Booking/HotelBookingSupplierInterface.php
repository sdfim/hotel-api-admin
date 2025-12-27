<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Booking;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingsMetadata;
use Modules\API\BookingAPI\ResponseModels\HotelBookResponseModel;
use Modules\API\BookingAPI\ResponseModels\HotelRetrieveBookingResponseModel;

interface HotelBookingSupplierInterface extends BaseBookingSupplierInterface
{
    // Booking Section
    /**
     * @return array<HotelBookResponseModel>|null
     */
    public function book(array $filters, ApiBookingInspector $bookingInspector): ?array;

    /**
     * @return array<HotelBookResponseModel>|null
     */
    public function cancelBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata): ?array;

    /**
     * @return array<HotelRetrieveBookingResponseModel>|null
     */
    public function retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, bool $isSync = false): ?array;

    public function listBookings(): ?array;

    // Change Booking Section
    public function availabilityChange(array $filters, string $type = 'default'): ?array;

    /**
     * @return array{
     *      result: array{
     *          incremental_total_price: float,
     *          current_booking_item: array{
     *              total_net: float,
     *              total_tax: float,
     *              total_fees: float,
     *              total_price: float,
     *              cancellation_policies: array,
     *              breakdown: array,
     *              rate_name: string,
     *              room_name: string,
     *              currency: string,
     *              booking_item: string,
     *              hotelier_booking_reference: string|null
     *          },
     *          new_booking_item: array{
     *              total_net: float,
     *              total_tax: float,
     *              total_fees: float,
     *              total_price: float,
     *              cancellation_policies: array,
     *              breakdown: array,
     *              rate_name: string,
     *              room_name: string,
     *              currency: string,
     *              booking_item: string
     *          }
     *      }
     *  }|null $data
     */
    public function priceCheck(array $filters): ?array;

    /**
     * Changes a booking based on the provided filters and mode.
     *
     * @param  array  $filters  {
     *                          booking_id: string,
     *                          booking_item: string,
     *                          new_booking_item?: string,
     *                          other_filters?: mixed
     *                          }
     * @return array|null {
     *                    status?: string,
     *                    Errors?: array<string, mixed>,
     *                    booking_item?: string,
     *                    supplier?: string
     *                    }
     */
    public function changeBooking(array $filters, string $type = 'soft'): ?array;
}
