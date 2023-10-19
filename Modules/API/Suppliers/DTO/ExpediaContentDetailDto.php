<?php

namespace Modules\API\Suppliers\DTO;

use Modules\API\ContentAPI\ResponseModels\ContentDetailResponse;

class ExpediaContentDetailDto
{

	public function ExpediaToContentDetailResponse(object $supplierResponse, int $giata_id) : array
	{
		$contentResponse = [];

		$hotelResponse = new ContentDetailResponse();
		$hotelResponse->setGiataHotelCode($giata_id);
		$hotelResponse->setImages($supplierResponse->images ?? []);
		$hotelResponse->setDescription($supplierResponse->description ?? '');
		$hotelResponse->setHotelName($supplierResponse->name);
		$hotelResponse->setDistance($supplierResponse->distance ?? '');
		$hotelResponse->setLatitude($supplierResponse->location['coordinates']['latitude']);
		$hotelResponse->setLongitude($supplierResponse->location['coordinates']['longitude']);
		$hotelResponse->setRating($supplierResponse->rating);
		$hotelResponse->setAmenities($supplierResponse->amenities ? array_map(function ($amenity) {
				return $amenity['name'];
			}, $supplierResponse->amenities) : []);
		$hotelResponse->setGiataDestination($supplierResponse->city ?? '');
		$hotelResponse->setUserRating($supplierResponse->rating ?? '');
		$hotelResponse->setSpecialInstructions($supplierResponse->room ?? []);
		$hotelResponse->setCheckInTime($supplierResponse->checkin_time ?? '');
		$hotelResponse->setCheckOutTime($supplierResponse->checkout_time ?? '');
		$hotelResponse->setHotelFees($supplierResponse->fees ?? []);
		$hotelResponse->setPolicies($supplierResponse->policies ?? []);
		$hotelResponse->setDescriptions($supplierResponse->descriptions ?? []);

		$contentResponse[] = $hotelResponse->toArray();

		return $contentResponse;
	}
}