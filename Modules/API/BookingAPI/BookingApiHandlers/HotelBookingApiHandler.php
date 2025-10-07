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
use Modules\API\BookingAPI\Controllers\BookingApiHandlerInterface;
use Modules\API\BookingAPI\Controllers\ExpediaHotelBookingApiController;
use Modules\API\BookingAPI\Controllers\HbsiHotelBookingApiController;
use Modules\API\BookingAPI\Controllers\HotelTraderHotelBookingApiController;
use Modules\API\Services\HotelBookingApiHandlerService;
use Modules\API\Services\HotelCombinationService;
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
        private readonly ExpediaHotelBookingApiController $expedia,
        private readonly HbsiHotelBookingApiController $hbsi,
        private readonly HotelTraderHotelBookingApiController $hTrader,
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

            if (($supplier === SupplierNameEnum::HBSI->value || $supplier === SupplierNameEnum::HOTEL_TRADER->value)
                && Cache::get('room_combinations:'.$request->booking_item)) {
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

            $data = match (SupplierNameEnum::from($supplier)) {
                SupplierNameEnum::EXPEDIA => $this->expedia->addItem($filters),
                SupplierNameEnum::HBSI => $this->hbsi->addItem($filters),
                SupplierNameEnum::HOTEL_TRADER => $this->hTrader->addItem($filters),
                default => [],
            };
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
                    Mail::to($email_verification)->queue(new BookingQuoteVerificationMail($verificationUrl, $denyUrl, $request->booking_item, $filters['api_client']));
                    $mailStatus = 'verification_email_queued';
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
            $data = match (SupplierNameEnum::from($supplier)) {
                SupplierNameEnum::EXPEDIA => $this->expedia->removeItem($filters),
                SupplierNameEnum::HBSI => $this->hbsi->removeItem($filters),
                SupplierNameEnum::HOTEL_TRADER => $this->hTrader->removeItem($filters),
                default => [],
            };

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
