<?php

namespace Modules\API\PushContent\Controllers;

use App\Http\Controllers\Controller;
use App\Models\HotelTraderContentHotel;
use App\Models\HotelTraderContentRatePlan;
use App\Models\HotelTraderContentRoomType;
use App\Models\HotelTraderContentCancellationPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\API\PushContent\Requests\HotelTraderContentHotelRequest;
use Modules\API\PushContent\Requests\HotelTraderContentRatePlanRequest;
use Modules\API\PushContent\Requests\HotelTraderContentRoomTypeRequest;
use Modules\API\PushContent\Requests\HotelTraderContentCancellationPolicyRequest;

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

            $hotel = HotelTraderContentHotel::create($mappedHotelData);

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
                ],
            ], 400);
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
                ],
            ], 401);
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
     * Updates an existing hotel in the database.
     */
    public function updateHotel(HotelTraderContentHotelRequest $request, string $code): JsonResponse
    {
        $messageId = Str::uuid()->toString();
        try {
            $hotel = HotelTraderContentHotel::where('code', $code)->first();

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
                ],
            ], 400);
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
                ],
            ], 401);
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
                $createdRoom = HotelTraderContentRoomType::create($this->mapRoomTypeKeysToSnakeCase($room));
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
            $room = HotelTraderContentRoomType::where('code', $code)->first();
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
                $createdRatePlan = HotelTraderContentRatePlan::create($this->mapRatePlanKeysToSnakeCase($rateplan));
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
            $rateplan = HotelTraderContentRatePlan::where('code', $code)->first();
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
                $createdPolicy = HotelTraderContentCancellationPolicy::create($this->mapCancellationPolicyKeysToSnakeCase($policy));
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
            $policy = HotelTraderContentCancellationPolicy::where('code', $code)->first();
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
            return !is_null($v);
        });
    }
}
