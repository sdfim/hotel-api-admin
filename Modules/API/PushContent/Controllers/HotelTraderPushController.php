<?php

namespace Modules\API\PushContent\Controllers;

use App\Http\Controllers\Controller;
use App\Models\HotelTraderContentCancellationPolicyPush;
//use App\Models\HotelTraderContentHotelPush;
use App\Models\HotelTraderContentHotelPush;
use App\Models\HotelTraderContentProductPush;
use App\Models\HotelTraderContentRatePlanPush;
use App\Models\HotelTraderContentRoomTypePush;
use App\Models\HotelTraderContentTax;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\API\PushContent\Requests\HotelTraderContentCancellationPolicyRequest;
use Modules\API\PushContent\Requests\HotelTraderContentHotelRequest;
use Modules\API\PushContent\Requests\HotelTraderContentProductRequest;
use Modules\API\PushContent\Requests\HotelTraderContentRatePlanRequest;
use Modules\API\PushContent\Requests\HotelTraderContentRoomTypeRequest;
use Modules\API\PushContent\Requests\HotelTraderContentTaxRequest;

class HotelTraderPushController extends Controller
{
    /**
     * Stores a new hotel in the database.
     */
    public function storeHotels(HotelTraderContentHotelRequest $request): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $updateType = $request->input('updateType');
            $hotelData = $request->input('hotel', []);

            $mappedHotelData = $this->mapHotelKeysToSnakeCase($hotelData);

            $hotel = HotelTraderContentHotelPush::create($mappedHotelData);

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Hotel created successfully.',
                ],
                'updateType' => $updateType,
                'hotel' => $hotel->code,
            ], 201);

        } catch (ValidationException $e) {
            // Get the hotel code from request or use default
            $objectCode = $request->input('hotel.code', '');

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Validation failed due to missing fields.',
                    'errors' => [
                        [
                            'objectCode' => $objectCode,
                            'errorCode' => '4005',
                            'errorMessage' => 'required field {field} missing',
                        ],
                    ],
                ], 400]);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Invalid Credentials',
                    'errors' => [
                        [
                            'errorCode' => '4001',
                            'errorMessage' => 'Authentication failed. Invalid credentials provided.',
                        ],
                    ],
                ], 401]);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                    'errors' => [
                        [
                            'errorCode' => '4099',
                            'errorMessage' => 'An unexpected error occurred.',
                        ],
                    ],
                ], 500]);
        }
    }

    /**
     * Updates an existing hotel in the database.
     */
    public function updateHotel(HotelTraderContentHotelRequest $request, string $code): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $hotel = HotelTraderContentHotelPush::where('code', $code)->first();

            if (! $hotel) {
                return response()->json([
                    'messageId' => $messageId,
                    'status' => [
                        'success' => false,
                        'message' => 'Hotel not found.',
                        'errors' => [
                            [
                                'objectCode' => $code,
                                'errorCode' => '4004',
                                'errorMessage' => 'The requested hotel was not found.',
                            ],
                        ],
                    ],
                ], 404);
            }

            $updateType = $request->input('updateType');
            $hotelData = $request->input('hotel', []);

            $mappedHotelData = $this->mapHotelKeysToSnakeCase($hotelData);

            $hotel->update($mappedHotelData);

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Hotel updated successfully.',
                ],
                'updateType' => $updateType,
                'hotel' => $hotel->code,
            ], 200);

        } catch (ValidationException $e) {
            // Get the hotel code from request or use default
            $objectCode = $request->input('hotel.code', 'HTPKG');

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Validation failed due to missing fields.',
                    'errors' => [
                        [
                            'objectCode' => $objectCode,
                            'errorCode' => '4005',
                            'errorMessage' => 'required field {field} missing',
                        ],
                    ],
                ], 400]);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Invalid Credentials',
                    'errors' => [
                        [
                            'errorCode' => '4001',
                            'errorMessage' => 'Authentication failed. Invalid credentials provided.',
                        ],
                    ],
                ], 401]);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                    'errors' => [
                        [
                            'errorCode' => '4099',
                            'errorMessage' => 'An unexpected error occurred.',
                        ],
                    ],
                ],
            ], 500);
        }
    }

    /**
     * Stores new room types in the database.
     */
    public function storeRoomTypes(HotelTraderContentRoomTypeRequest $request): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $updateType = $request->input('updateType');
            $propertyCode = $request->input('propertyCode');
            $rooms = $request->input('rooms', []);
            $created = [];
            foreach ($rooms as $room) {
                $room['hotel_code'] = $propertyCode;
                $room['bedtypes'] = Arr::get($room, 'bedtypes', []);
                $room['amenities'] = Arr::get($room, 'amenities', []);
                $createdRoom = HotelTraderContentRoomTypePush::create($this->mapRoomTypeKeysToSnakeCase($room));
                $created[] = $createdRoom->code;
            }

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Room types created successfully.',
                ],
                'updateType' => $updateType,
                'rooms' => $created,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Validation failed due to missing fields.',
                    'errors' => $e->errors(),
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                ],
            ], 500);
        }
    }

    /**
     * Updates an existing room type in the database.
     */
    public function updateRoomType(HotelTraderContentRoomTypeRequest $request, string $code): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $code = $request->input('room.code', '');
            $room = HotelTraderContentRoomTypePush::where('code', $code)->first();
            if (! $room) {
                return response()->json([
                    'messageId' => $messageId,
                    'status' => [
                        'success' => false,
                        'message' => 'Room type not found.',
                    ],
                ], 404);
            }
            $roomData = $request->all();
            $roomData['bedtypes'] = Arr::get($roomData, 'bedtypes', []);
            $roomData['amenities'] = Arr::get($roomData, 'amenities', []);
            $room->update($this->mapRoomTypeKeysToSnakeCase($roomData));

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Room type updated successfully.',
                ],
                'room' => $room->code,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Validation failed due to missing fields.',
                    'errors' => $e->errors(),
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                ],
            ], 500);
        }
    }

    /**
     * Stores new rate plans in the database.
     */
    public function storeRatePlans(HotelTraderContentRatePlanRequest $request): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $updateType = $request->input('updateType');
            $propertyCode = $request->input('propertyCode');
            $rateplans = $request->input('rateplans', []);
            $created = [];
            foreach ($rateplans as $rateplan) {
                $rateplan['hotel_code'] = $propertyCode;
                $rateplan['currency'] = Arr::get($rateplan, 'currency', []);
                $rateplan['mealplan'] = Arr::get($rateplan, 'mealplan', []);
                $rateplan['rateplan_type'] = Arr::get($rateplan, 'rateplanType', []);
                $rateplan['destination_exclusive'] = Arr::get($rateplan, 'destinationExclusive', []);
                $rateplan['destination_restriction'] = Arr::get($rateplan, 'destinationRestriction', null);
                $rateplan['seasonal_policies'] = Arr::get($rateplan, 'seasonalPolicies', []);
                $rateplan['short_description'] = $rateplan['shortDescription'] ?? null;
                $rateplan['detail_description'] = $rateplan['detailDescription'] ?? null;
                $rateplan['cancellation_policy_code'] = $rateplan['cancellationPolicyCode'] ?? null;
                $rateplan['is_tax_inclusive'] = $rateplan['isTaxInclusive'] ?? false;
                $rateplan['is_refundable'] = $rateplan['isRefundable'] ?? false;
                $rateplan['is_promo'] = $rateplan['isPromo'] ?? false;
                $createdRatePlan = HotelTraderContentRatePlanPush::create($this->mapRatePlanKeysToSnakeCase($rateplan));
                $created[] = $createdRatePlan->code;
            }

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Rate plans created successfully.',
                ],
                'updateType' => $updateType,
                'rateplans' => $created,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Validation failed due to missing fields.',
                    'errors' => $e->errors(),
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                ],
            ], 500);
        }
    }

    /**
     * Updates an existing rate plan in the database.
     */
    public function updateRatePlan(HotelTraderContentRatePlanRequest $request, string $code): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $code = $request->input('rateplan.code', $code);
            $rateplan = HotelTraderContentRatePlanPush::where('code', $code)->first();
            if (! $rateplan) {
                return response()->json([
                    'messageId' => $messageId,
                    'status' => [
                        'success' => false,
                        'message' => 'Rate plan not found.',
                    ],
                ], 404);
            }
            $rateplanData = $request->input('rateplan', []);
            $rateplanData['hotel_code'] = $request->input('propertyCode');
            $rateplanData['currency'] = Arr::get($rateplanData, 'currency', []);
            $rateplanData['mealplan'] = Arr::get($rateplanData, 'mealplan', []);
            $rateplanData['rateplan_type'] = Arr::get($rateplanData, 'rateplanType', []);
            $rateplanData['destination_exclusive'] = Arr::get($rateplanData, 'destinationExclusive', []);
            $rateplanData['destination_restriction'] = Arr::get($rateplanData, 'destinationRestriction', null);
            $rateplanData['seasonal_policies'] = Arr::get($rateplanData, 'seasonalPolicies', []);
            $rateplanData['short_description'] = $rateplanData['shortDescription'] ?? null;
            $rateplanData['detail_description'] = $rateplanData['detailDescription'] ?? null;
            $rateplanData['cancellation_policy_code'] = $rateplanData['cancellationPolicyCode'] ?? null;
            $rateplanData['is_tax_inclusive'] = $rateplanData['isTaxInclusive'] ?? false;
            $rateplanData['is_refundable'] = $rateplanData['isRefundable'] ?? false;
            $rateplanData['is_promo'] = $rateplanData['isPromo'] ?? false;
            $rateplan->update($this->mapRatePlanKeysToSnakeCase($rateplanData));

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Rate plan updated successfully.',
                ],
                'rateplan' => $rateplan->code,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Validation failed due to missing fields.',
                    'errors' => $e->errors(),
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                ],
            ], 500);
        }
    }

    /**
     * Stores new cancellation policies in the database.
     */
    public function storeCancellationPolicies(HotelTraderContentCancellationPolicyRequest $request): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $updateType = $request->input('updateType');
            $propertyCode = $request->input('propertyCode');
            $policies = $request->input('cancellationPolicies', []);
            $created = [];
            foreach ($policies as $policy) {
                $policy['hotel_code'] = $propertyCode;
                $policy['penalty_windows'] = Arr::get($policy, 'penaltyWindows', []);
                $createdPolicy = HotelTraderContentCancellationPolicyPush::create($this->mapCancellationPolicyKeysToSnakeCase($policy));
                $created[] = $createdPolicy->code;
            }

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Cancellation policies created successfully.',
                ],
                'updateType' => $updateType,
                'cancellationPolicies' => $created,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Validation failed due to missing fields.',
                    'errors' => $e->errors(),
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                ],
            ], 500);
        }
    }

    /**
     * Updates an existing cancellation policy in the database.
     */
    public function updateCancellationPolicie(HotelTraderContentCancellationPolicyRequest $request, string $code): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $code = $request->input('cancellationPolicy.code', $code);
            $policy = HotelTraderContentCancellationPolicyPush::where('code', $code)->first();
            if (! $policy) {
                return response()->json([
                    'messageId' => $messageId,
                    'status' => [
                        'success' => false,
                        'message' => 'Cancellation policy not found.',
                    ],
                ], 404);
            }
            $policyData = $request->input('cancellationPolicy', []);
            $policyData['hotel_code'] = $request->input('propertyCode');
            $policyData['penalty_windows'] = Arr::get($policyData, 'penaltyWindows', []);
            $policy->update($this->mapCancellationPolicyKeysToSnakeCase($policyData));

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Cancellation policy updated successfully.',
                ],
                'cancellationPolicy' => $policy->code,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Validation failed due to missing fields.',
                    'errors' => $e->errors(),
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                ],
            ], 500);
        }
    }

    /**
     * Stores new taxes in the database.
     */
    public function storeTaxes(HotelTraderContentTaxRequest $request): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $updateType = $request->input('updateType');
            $propertyCode = $request->input('propertyCode');
            $taxes = $request->input('taxes', []);
            $created = [];
            foreach ($taxes as $tax) {
                $tax['hotel_code'] = $propertyCode;
                $tax['percent_or_flat'] = $tax['percentOrFlat'] ?? null;
                $tax['charge_frequency'] = $tax['chargeFrequency'] ?? null;
                $tax['charge_basis'] = $tax['chargeBasis'] ?? null;
                $tax['tax_type'] = $tax['taxType'] ?? null;
                $tax['applies_to_children'] = $tax['appliesToChildren'] ?? false;
                $tax['pay_at_property'] = $tax['payAtProperty'] ?? false;
                $createdTax = HotelTraderContentTax::create($this->mapTaxKeysToSnakeCase($tax));
                $created[] = $createdTax->code;
            }

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Taxes created successfully.',
                ],
                'updateType' => $updateType,
                'taxes' => $created,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Validation failed due to missing fields.',
                    'errors' => $e->errors(),
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                ],
            ], 500);
        }
    }

    /**
     * Updates an existing tax in the database.
     */
    public function updateTax(HotelTraderContentTaxRequest $request, string $code): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $code = $request->input('tax.code', $code);
            $tax = HotelTraderContentTax::where('code', $code)->first();
            if (! $tax) {
                return response()->json([
                    'messageId' => $messageId,
                    'status' => [
                        'success' => false,
                        'message' => 'Tax not found.',
                    ],
                ], 404);
            }
            $taxData = $request->input('tax', []);
            $taxData['hotel_code'] = $request->input('propertyCode');
            $taxData['percent_or_flat'] = $taxData['percentOrFlat'] ?? null;
            $taxData['charge_frequency'] = $taxData['chargeFrequency'] ?? null;
            $taxData['charge_basis'] = $taxData['chargeBasis'] ?? null;
            $taxData['tax_type'] = $taxData['taxType'] ?? null;
            $taxData['applies_to_children'] = $taxData['appliesToChildren'] ?? false;
            $taxData['pay_at_property'] = $taxData['payAtProperty'] ?? false;
            $tax->update($this->mapTaxKeysToSnakeCase($taxData));

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Tax updated successfully.',
                ],
                'tax' => $tax->code,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Validation failed due to missing fields.',
                    'errors' => $e->errors(),
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                ],
            ], 500);
        }
    }

    /**
     * Audits a hotel and returns its metadata if found.
     */
    public function auditHotel(\Illuminate\Http\Request $request): JsonResponse
    {
        $messageId = $request->input('messageId');
        $propertyCode = $request->input('propertyCode');

        if (! $messageId || ! $propertyCode) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Missing required fields.',
                ],
            ], 400);
        }

        $hotel = HotelTraderContentHotelPush::where('code', $propertyCode)->first();
        if (! $hotel) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Hotel not found.',
                ],
            ], 404);
        }

        // Convert model to array and map to API response keys
        $hotelArray = $hotel->toArray();
        $hotelResponse = [
            'code' => $hotelArray['code'] ?? null,
            'name' => $hotelArray['name'] ?? null,
            'mappingProvider' => $hotelArray['mapping_providers'] ?? null,
            'mappingCode' => $hotelArray['mapping_code'] ?? null,
            'starRating' => $hotelArray['star_rating'] ?? null,
            'defaultCurrencyCode' => $hotelArray['default_currency_code'] ?? null,
            'maxRoomsBookable' => $hotelArray['max_rooms_bookable'] ?? null,
            'numberOfRooms' => $hotelArray['number_of_rooms'] ?? null,
            'numberOfFloors' => $hotelArray['number_of_floors'] ?? null,
            'addressLine1' => $hotelArray['address_line_1'] ?? null,
            'addressLine2' => $hotelArray['address_line_2'] ?? null,
            'city' => $hotelArray['city'] ?? null,
            'state' => $hotelArray['state'] ?? null,
            'stateCode' => $hotelArray['state_code'] ?? null,
            'country' => $hotelArray['country'] ?? null,
            'countryCode' => $hotelArray['country_code'] ?? null,
            'zip' => $hotelArray['zip'] ?? null,
            'phone1' => $hotelArray['phone_1'] ?? null,
            'phone2' => $hotelArray['phone_2'] ?? null,
            'fax1' => $hotelArray['fax_1'] ?? null,
            'fax2' => $hotelArray['fax_2'] ?? null,
            'websiteUrl' => $hotelArray['website_url'] ?? null,
            'longitude' => $hotelArray['longitude'] ?? null,
            'latitude' => $hotelArray['latitude'] ?? null,
            'longDescription' => $hotelArray['long_description'] ?? null,
            'shortDescription' => $hotelArray['short_description'] ?? null,
            'checkInPolicy' => $hotelArray['check_in_policy'] ?? null,
            'checkInTime' => $hotelArray['check_in_time'] ?? null,
            'checkOutTime' => $hotelArray['check_out_time'] ?? null,
            'adultAge' => $hotelArray['adult_age'] ?? null,
            'timeZone' => $hotelArray['time_zone'] ?? null,
            'defaultLanguage' => $hotelArray['default_language'] ?? null,
            'adultOnly' => $hotelArray['adult_only'] ?? null,
            'currencies' => $hotelArray['currencies'] ?? [],
            'languages' => $hotelArray['languages'] ?? [],
            'creditCardTypes' => $hotelArray['credit_card_types'] ?? [],
            'bedtypes' => $hotelArray['bed_types'] ?? [],
            'amenities' => $hotelArray['amenities'] ?? [],
            'ageCategories' => $hotelArray['age_categories'] ?? [],
        ];

        return response()->json([
            'messageId' => $messageId,
            'status' => [
                'success' => true,
            ],
            'hotel' => $hotelResponse,
        ], 200);
    }

    /**
     * Audits room types for a hotel and returns their metadata if found.
     */
    public function auditRoomtype(\Illuminate\Http\Request $request): JsonResponse
    {
        $messageId = $request->input('messageId');
        $propertyCode = $request->input('propertyCode');
        $roomCodes = $request->input('roomCodes');

        if (! $messageId || ! $propertyCode) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Missing required fields.',
                ],
                'propertyCode' => $propertyCode,
            ], 400);
        }

        $query = HotelTraderContentRoomType::where('hotel_code', $propertyCode);
        if (is_array($roomCodes) && count($roomCodes) > 0) {
            $query->whereIn('code', $roomCodes);
        }
        $rooms = $query->get();

        $roomsResponse = $rooms->map(function ($room) {
            return [
                'roomName' => $room->name,
                'roomCode' => $room->code,
                'longDescription' => $room->long_description,
                'shortDescription' => $room->short_description,
                'maxAdultOccupancy' => $room->max_adult_occupancy,
                'minAdultOccupancy' => $room->min_adult_occupancy,
                'maxChildOccupancy' => $room->max_child_occupancy,
                'minChildOccupancy' => $room->min_child_occupancy,
                'totalMaxOccupancy' => $room->total_max_occupancy,
                'maxOccupancyForDefaultPrice' => $room->max_occupancy_for_default_price,
                'bedtypes' => $room->bedtypes ?? [],
                'amenities' => $room->amenities ?? [],
                'images' => $room->images ?? [],
            ];
        })->toArray();

        return response()->json([
            'messageId' => $messageId,
            'status' => [
                'success' => true,
            ],
            'propertyCode' => $propertyCode,
            'rooms' => $roomsResponse,
        ], 200);
    }

    /**
     * Audits rate plans for a hotel and returns their metadata if found.
     */
    public function auditRateplan(\Illuminate\Http\Request $request): JsonResponse
    {
        $messageId = $request->input('messageId');
        $propertyCode = $request->input('propertyCode');
        $rateplanCodes = $request->input('rateplanCodes');

        if (! $messageId || ! $propertyCode) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Missing required fields.',
                ],
                'propertyCode' => $propertyCode,
            ], 400);
        }

        $query = HotelTraderContentRatePlan::where('hotel_code', $propertyCode);
        if (is_array($rateplanCodes) && count($rateplanCodes) > 0) {
            $query->whereIn('code', $rateplanCodes);
        }
        $rateplans = $query->get();

        $rateplansResponse = $rateplans->map(function ($rateplan) {
            return [
                'name' => $rateplan->name,
                'code' => $rateplan->code,
                'currency' => $rateplan->currency ?? null,
                'shortDescription' => $rateplan->short_description,
                'detailDescription' => $rateplan->detail_description,
                'cancellationPolicyCode' => $rateplan->cancellation_policy_code,
                'isTaxInclusive' => $rateplan->is_tax_inclusive,
                'mealplan' => $rateplan->mealplan ?? null,
                'isRefundable' => $rateplan->is_refundable,
                'rateplanType' => $rateplan->rateplan_type ?? [],
                'isPromo' => $rateplan->is_promo,
                'destinationExclusive' => $rateplan->destination_exclusive ?? null,
                'destinationRestriction' => $rateplan->destination_restriction ?? null,
                'seasonalPolicies' => $rateplan->seasonal_policies ?? [],
            ];
        })->toArray();

        return response()->json([
            'messageId' => $messageId,
            'status' => [
                'success' => true,
            ],
            'propertyCode' => $propertyCode,
            'rateplans' => $rateplansResponse,
        ], 200);
    }

    /**
     * Audits cancellation policies for a hotel and returns their metadata if found.
     */
    public function auditCancellationPolicy(\Illuminate\Http\Request $request): JsonResponse
    {
        $messageId = $request->input('messageId');
        $propertyCode = $request->input('propertyCode');
        $cancellationPolicyCodes = $request->input('cancellationPolicyCodes');

        if (! $messageId || ! $propertyCode) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Missing required fields.',
                ],
                'propertyCode' => $propertyCode,
            ], 400);
        }

        $query = HotelTraderContentCancellationPolicyPush::where('hotel_code', $propertyCode);
        if (is_array($cancellationPolicyCodes) && count($cancellationPolicyCodes) > 0) {
            $query->whereIn('code', $cancellationPolicyCodes);
        }
        $policies = $query->get();

        $policiesResponse = $policies->map(function ($policy) {
            return [
                'code' => $policy->code,
                'name' => $policy->name,
                'description' => $policy->description,
                'penaltyWindows' => $policy->penalty_windows ?? [],
            ];
        })->toArray();

        return response()->json([
            'messageId' => $messageId,
            'status' => [
                'success' => true,
            ],
            'propertyCode' => $propertyCode,
            'cancellationPolicies' => $policiesResponse,
        ], 200);
    }

    /**
     * Audits taxes for a hotel and returns their metadata if found.
     */
    public function auditTax(\Illuminate\Http\Request $request): JsonResponse
    {
        $messageId = $request->input('messageId');
        $propertyCode = $request->input('propertyCode');
        $taxCodes = $request->input('taxCodes');

        if (! $messageId || ! $propertyCode) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Missing required fields.',
                ],
                'propertyCode' => $propertyCode,
            ], 400);
        }

        $query = HotelTraderContentTax::where('hotel_code', $propertyCode);
        if (is_array($taxCodes) && count($taxCodes) > 0) {
            $query->whereIn('code', $taxCodes);
        }
        $taxes = $query->get();

        $taxesResponse = $taxes->map(function ($tax) {
            return [
                'code' => $tax->code,
                'name' => $tax->name,
                'percentOrFlat' => $tax->percent_or_flat,
                'chargeFrequency' => $tax->charge_frequency,
                'chargeBasis' => $tax->charge_basis,
                'value' => $tax->value,
                'taxType' => $tax->tax_type,
                'appliesToChildren' => $tax->applies_to_children,
                'payAtProperty' => $tax->pay_at_property,
            ];
        })->toArray();

        return response()->json([
            'messageId' => $messageId,
            'status' => [
                'success' => true,
            ],
            'propertyCode' => $propertyCode,
            'taxes' => $taxesResponse,
        ], 200);
    }

    /**
     * Stores new products (rateplan-roomtype-tax relations) in the database.
     */
    public function storeProducts(HotelTraderContentProductRequest $request): JsonResponse
    {
        $messageId = $request->input('messageId', Str::uuid()->toString());
        $propertyCode = $request->input('propertyCode');
        $rateplanCode = $request->input('rateplanCode');
        $products = $request->input('products', []);
        $created = [];
        try {
            foreach ($products as $product) {
                $data = [
                    'hotel_code' => $propertyCode,
                    'rateplan_code' => $product['rateplanCode'],
                    'roomtype_code' => $product['roomtypeCode'],
                    'taxes' => $product['taxes'],
                ];
                $model = HotelTraderContentProduct::updateOrCreate(
                    [
                        'hotel_code' => $propertyCode,
                        'rateplan_code' => $product['rateplanCode'],
                        'roomtype_code' => $product['roomtypeCode'],
                    ],
                    $data
                );
                $created[] = [
                    'rateplanCode' => $model->rateplan_code,
                    'roomtypeCode' => $model->roomtype_code,
                    'taxes' => $model->taxes,
                ];
            }

            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => true,
                    'message' => 'Products created/updated successfully.',
                ],
                'propertyCode' => $propertyCode,
                'products' => $created,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Internal Server Error',
                ],
            ], 500);
        }
    }

    /**
     * Audits products for a hotel and returns their metadata if found.
     */
    public function auditProducts(\Illuminate\Http\Request $request): JsonResponse
    {
        $messageId = $request->input('messageId');
        $propertyCode = $request->input('propertyCode');
        $rateplanCodes = $request->input('rateplanCodes');

        if (! $messageId || ! $propertyCode) {
            return response()->json([
                'messageId' => $messageId,
                'status' => [
                    'success' => false,
                    'message' => 'Missing required fields.',
                ],
                'propertyCode' => $propertyCode,
            ], 400);
        }

        $query = HotelTraderContentProduct::where('hotel_code', $propertyCode);
        if (is_array($rateplanCodes) && count($rateplanCodes) > 0) {
            $query->whereIn('rateplan_code', $rateplanCodes);
        }
        $products = $query->get();

        $productsResponse = $products->map(function ($product) {
            return [
                'rateplanCode' => $product->rateplan_code,
                'roomtypeCode' => $product->roomtype_code,
                'taxes' => $product->taxes ?? [],
            ];
        })->toArray();

        return response()->json([
            'messageId' => $messageId,
            'propertyCode' => $propertyCode,
            'status' => [
                'success' => true,
            ],
            'products' => $productsResponse,
        ], 200);
    }

    /**
     * Maps camelCase hotel keys to snake_case for the model.
     */
    private function mapHotelKeysToSnakeCase(array $hotel): array
    {
        $map = [
            'code' => 'code',
            'mappingProviders' => 'mapping_providers',
            'name' => 'name',
            'starRating' => 'star_rating',
            'defaultCurrencyCode' => 'default_currency_code',
            'maxRoomsBookable' => 'max_rooms_bookable',
            'numberOfRooms' => 'number_of_rooms',
            'numberOfFloors' => 'number_of_floors',
            'addressLine1' => 'address_line_1',
            'addressLine2' => 'address_line_2',
            'city' => 'city',
            'state' => 'state',
            'stateCode' => 'state_code',
            'country' => 'country',
            'countryCode' => 'country_code',
            'zip' => 'zip',
            'phone1' => 'phone_1',
            'phone2' => 'phone_2',
            'fax1' => 'fax_1',
            'fax2' => 'fax_2',
            'websiteUrl' => 'website_url',
            'longitude' => 'longitude',
            'latitude' => 'latitude',
            'longDescription' => 'long_description',
            'shortDescription' => 'short_description',
            'checkInTime' => 'check_in_time',
            'checkOutTime' => 'check_out_time',
            'timeZone' => 'time_zone',
            'adultAge' => 'adult_age',
            'defaultLanguage' => 'default_language',
            'adultOnly' => 'adult_only',
            'currencies' => 'currencies',
            'languages' => 'languages',
            'creditCardTypes' => 'credit_card_types',
            'bedtypes' => 'bed_types',
            'amenities' => 'amenities',
            'ageCategories' => 'age_categories',
            'checkInPolicy' => 'check_in_policy',
            'images' => 'images',
        ];

        $result = [];
        foreach ($map as $camel => $snake) {
            if (array_key_exists($camel, $hotel)) {
                $result[$snake] = $hotel[$camel];
            }
        }

        return $result;
    }

    /**
     * Helper to map room type keys to snake_case.
     */
    private function mapRoomTypeKeysToSnakeCase(array $data): array
    {
        $map = [
            'hotel_code' => $data['hotel_code'] ?? null,
            'code' => $data['code'] ?? null,
            'name' => $data['name'] ?? null,
            'long_description' => $data['longDescription'] ?? $data['long_description'] ?? null,
            'short_description' => $data['shortDescription'] ?? $data['short_description'] ?? null,
            'max_adult_occupancy' => $data['maxAdultOccupancy'] ?? $data['max_adult_occupancy'] ?? null,
            'min_adult_occupancy' => $data['minAdultOccupancy'] ?? $data['min_adult_occupancy'] ?? null,
            'max_child_occupancy' => $data['maxChildOccupancy'] ?? $data['max_child_occupancy'] ?? null,
            'min_child_occupancy' => $data['minChildOccupancy'] ?? $data['min_child_occupancy'] ?? null,
            'total_max_occupancy' => $data['totalMaxOccupancy'] ?? $data['total_max_occupancy'] ?? null,
            'max_occupancy_for_default_price' => $data['maxOccupancyForDefaultPrice'] ?? $data['max_occupancy_for_default_price'] ?? null,
            'bedtypes' => $data['bedtypes'] ?? null,
            'amenities' => $data['amenities'] ?? null,
        ];

        return array_filter($map, function ($v) {
            return ! is_null($v);
        });
    }

    /**
     * Helper to map rate plan keys to snake_case.
     */
    private function mapRatePlanKeysToSnakeCase(array $data): array
    {
        $map = [
            'hotel_code' => $data['hotel_code'] ?? null,
            'code' => $data['code'] ?? null,
            'name' => $data['name'] ?? null,
            'currency' => $data['currency'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'detail_description' => $data['detail_description'] ?? null,
            'cancellation_policy_code' => $data['cancellation_policy_code'] ?? null,
            'mealplan' => $data['mealplan'] ?? null,
            'is_tax_inclusive' => $data['is_tax_inclusive'] ?? false,
            'is_refundable' => $data['is_refundable'] ?? false,
            'rateplan_type' => $data['rateplan_type'] ?? null,
            'is_promo' => $data['is_promo'] ?? false,
            'destination_exclusive' => $data['destination_exclusive'] ?? null,
            'destination_restriction' => $data['destination_restriction'] ?? null,
            'seasonal_policies' => $data['seasonal_policies'] ?? null,
        ];

        return array_filter($map, function ($v) {
            return ! is_null($v);
        });
    }

    /**
     * Helper to map cancellation policy keys to snake_case.
     */
    private function mapCancellationPolicyKeysToSnakeCase(array $data): array
    {
        $map = [
            'hotel_code' => $data['hotel_code'] ?? null,
            'code' => $data['code'] ?? null,
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'penalty_windows' => $data['penalty_windows'] ?? null,
        ];

        return array_filter($map, function ($v) {
            return ! is_null($v);
        });
    }

    /**
     * Helper to map tax keys to snake_case.
     */
    private function mapTaxKeysToSnakeCase(array $data): array
    {
        $map = [
            'hotel_code' => $data['hotel_code'] ?? null,
            'code' => $data['code'] ?? null,
            'name' => $data['name'] ?? null,
            'percent_or_flat' => $data['percentOrFlat'] ?? null,
            'charge_frequency' => $data['chargeFrequency'] ?? null,
            'charge_basis' => $data['chargeBasis'] ?? null,
            'value' => $data['value'] ?? null,
            'tax_type' => $data['taxType'] ?? null,
            'applies_to_children' => $data['appliesToChildren'] ?? false,
            'pay_at_property' => $data['payAtProperty'] ?? false,
        ];

        return array_filter($map, function ($v) {
            return ! is_null($v);
        });
    }
}
