<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use Exception;
use Modules\API\BaseController;
use Modules\API\Requests\BookingAddItemHotelRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\Suppliers\ExpediaSupplier\ExpediaService;
use Illuminate\Support\Facades\Validator;
use Modules\Inspector\SearchInspectorController;
use Modules\API\BookingAPI\ExpediaHotelBookingApiHandler;

class HotelBookingApiHandler extends BaseController // implements BookingApiHandlerInterface
{
    /**
     * @var ExpediaService
     */
    private ExpediaService $expediaService;
    /**
     * @var SearchInspectorController
     */
    private SearchInspectorController $apiInspector;
    /**
     * @var ExpediaHotelBookingApiHandler
     */
    private ExpediaHotelBookingApiHandler $expedia;
    /**
     *
     */
    private const EXPEDIA_SUPPLIER_NAME = 'Expedia';

    /**
     * @param ExpediaService $expediaService
     */
    public function __construct(ExpediaService $expediaService)
    {
        $this->expediaService = $expediaService;
        $this->apiInspector = new SearchInspectorController();
        $this->expedia = new ExpediaHotelBookingApiHandler($this->expediaService);
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function addItem(Request $request, string $supplier): JsonResponse
    {
        $data = [];
        try {
            $bookingAddItemRequest = new BookingAddItemHotelRequest();
            $rules = $bookingAddItemRequest->rules();
            $filters = Validator::make($request->all(), $rules)->validated();
            $filters = array_merge($filters, $request->all());

            if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                $data = $this->expedia->addItem($filters);
            }
            // TODO: Add other suppliers
        } catch (Exception $e) {
            \Log::error('HotelBookingApiHandler | addItem ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        if (isset($data['errors'])) return $this->sendError($data['errors'], $data['message']);

        return $this->sendResponse($data, 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function removeItem(Request $request, string $supplier): JsonResponse
    {
        try {
            // TODO: add validation for request
            $filters = $request->all();

            $data = [];
            if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                $data = $this->expedia->removeItem($filters);
            }
            // TODO: Add other suppliers

        } catch (Exception $e) {
            \Log::error('HotelBookingApiHandler | removeItem ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        if (isset($data['error'])) return $this->sendError($data['error']);

        return $this->sendResponse(['result' => $data['success']], 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function retrieveItems(Request $request, string $supplier): JsonResponse
    {
        try {
            // TODO: add validation for request
            $filters = $request->all();

            $data = [];
            if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                $data = $this->expedia->retrieveItems($filters);
            }
            // TODO: Add other suppliers

        } catch (Exception $e) {
            \Log::error('HotelBookingApiHandler | retrieveItems ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        return $this->sendResponse(['result' => $data], 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function addPassengers(Request $request, string $supplier): JsonResponse
    {
        try {
            // TODO: add validation for request
            $filters = $request->all();

            $data = [];
            if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                $data = $this->expedia->addPassengers($filters);
            }
            // TODO: Add other suppliers

        } catch (Exception $e) {
            \Log::error('HotelBookingApiHandler | listBookings ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        return $this->sendResponse(['count' => count($data), 'result' => $data], 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function changeItems(Request $request, string $supplier): JsonResponse
    {
        try {
            // TODO: add validation for request
            $filters = $request->all();

            $data = [];
            if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                $data = $this->expedia->changeItems($filters);
            }
            // TODO: Add other suppliers

        } catch (Exception $e) {
            \Log::error('HotelBookingApiHandler | listBookings ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        if (isset($data['errors'])) return $this->sendError($data['errors'], $data['message']);
        return $this->sendResponse($data ?? [], 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function book(Request $request, string $supplier): JsonResponse
    {

    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function listBookings(Request $request, string $supplier): JsonResponse
    {
        try {
            // TODO: add validation for request
            $filters = $request->all();

            $data = [];
            if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                $data = $this->expedia->listBookings();
            }
            // TODO: Add other suppliers
        } catch (Exception $e) {
            \Log::error('HotelBookingApiHanlder | listBookings ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        return $this->sendResponse(['count' => count($data), 'result' => $data], 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function retrieveBooking(Request $request, string $supplier): JsonResponse
    {

    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function cancelBooking(Request $request, string $supplier): JsonResponse
    {
        try {
            // TODO: add validation for request
            $filters = $request->all();

            $data = [];
            if ($supplier == self::EXPEDIA_SUPPLIER_NAME) {
                $data = $this->expedia->cancelBooking($filters);
            }
            // TODO: Add other suppliers

        } catch (Exception $e) {
            \Log::error('HotelBookingApiHanlder | removeItem ' . $e->getMessage());
            return $this->sendError(['error' => $e->getMessage()], 'failed');
        }

        if (isset($data['error'])) return $this->sendError($data['error']);

        return $this->sendResponse(['result' => $data['success']], 'success');
    }
}
