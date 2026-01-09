<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use App\Mail\BookingQuoteVerificationMail;
use App\Models\ApiBookingItemCache;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingItemRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\API\BaseController;
use Modules\API\Services\HotelBookingApiHandlerService;
use Modules\API\Services\HotelCombinationService;
use Modules\API\Suppliers\Contracts\Hotel\Booking\HotelBookingSupplierRegistry;
use Modules\Enums\SupplierNameEnum;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @OA\PathItem(
 * path="/api/booking",
 * )
 */
class HotelBookingApiHandler extends BaseController implements BookingApiHandlerInterface
{
    public function __construct(
        private readonly HotelBookingSupplierRegistry $supplierRegistry,
    ) {}

    public function addItem(Request $request, string $supplier): JsonResponse
    {
        $filters = $request->all();
        $data = [];
        try {
            if (request()->has('booking_id')) {

                if (BookingRepository::isBook($request->booking_id, $request->booking_item)) {
                    return $this->sendError(
                        'booking_id - this cart is not available. This cart is at the booking stage or beyond.',
                    );
                }

                if (BookingRepository::isDuplicate($request->booking_id, $request->booking_item)) {
                    return $this->sendError('booking_item, booking_id pair is not unique. This item is already in your cart.');
                }

                if (ApiBookingInspectorRepository::isBookById($request->booking_id)) {
                    return $this->sendError(
                        'The order cart (booking_id) is already booked. You cannot add new items to this cart.'
                    );
                }

                $filters['booking_id'] = $request->booking_id;
            }

            if ($supplier != SupplierNameEnum::EXPEDIA->value && Cache::get('room_combinations:'.$request->booking_item)) {
                $hotelService = new HotelCombinationService($supplier);
                $hotelService->updateBookingItemsData($request->booking_item);
            }

            if (! ApiBookingItemRepository::isComleteCache($request->booking_item)) {
                return $this->sendError('booking_item - this item is single');
            }

            $apiBookingItem = ApiBookingItemCache::where('booking_item', $request->booking_item)->first()->toArray();
            $booking_item_data = json_decode($apiBookingItem['booking_item_data'], true);
            $filters['search_id'] = $apiBookingItem['search_id'];

            $filters = array_merge($filters, $request->all());
            $filters = array_merge($filters, $booking_item_data);

            app(HotelBookingApiHandlerService::class)->refreshFiltersByApiUser($filters, $request);

            $data = $this->supplierRegistry->get(SupplierNameEnum::from($supplier))->addItem($filters, $supplier);
            // Отправка письма с подтверждением, если требуется
            $email_verification = $request->input('email_verification', false);
            if ($email_verification) {
                $uuid = Str::uuid()->toString();
                $cacheKey = 'booking_email_verification:'.$request->booking_item.':'.$uuid;
                // Store the uuid and booking_item in cache for 1 week
                Cache::put($cacheKey, $request->booking_item, now()->addWeek());
                $verificationUrl = route('booking.verify', ['booking_item' => $request->booking_item, 'uuid' => $uuid]);
                $denyUrl = route('booking.deny', ['booking_item' => $request->booking_item, 'uuid' => $uuid]);
                try {
                    if (! Cache::has('bookingItem_no_mail_'.$request->booking_item)) {
                        Mail::to($email_verification)->queue(new BookingQuoteVerificationMail($verificationUrl, $denyUrl, $request->booking_item, $filters['api_client']));
                        $mailStatus = 'verification_email_queued';
                    } else {
                        $mailStatus = 'verification_email_skipped_by_test_flag';
                    }
                } catch (\Throwable $mailException) {
                    Log::error('Email verification queue error: '.$mailException->getMessage());
                    $mailStatus = 'verification_email_queue_failed';
                }
            } else {
                $mailStatus = null;
            }
        } catch (Exception|NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error('HotelBookingApiHandler | addItem '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }

        if (isset($data['errors'])) {
            return $this->sendError($data['errors'], $data['message']);
        }

        $data['mail_status'] = $mailStatus;

        $response = $this->sendResponse($data, 'success');

        return $response;
    }

    public function removeItem(Request $request, string $supplier): JsonResponse
    {
        $filters = $request->all();
        try {
            $data = $this->supplierRegistry->get(SupplierNameEnum::from($supplier))->removeItem($filters);
        } catch (Exception $e) {
            Log::error('HotelBookingApiHandler | removeItem '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return $this->sendError($e->getMessage(), 'failed');
        }

        if (isset($data['error'])) {
            return $this->sendError($data['error']);
        }

        return $this->sendResponse(['result' => $data['success']], 'success');
    }
}
