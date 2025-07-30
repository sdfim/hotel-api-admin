<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentDetailRoomsResponseFactory
{
    public static function create(): ContentDetailRoomsResponse
    {
        /** @var ContentDetailRoomsResponse $contentDetailRoomsResponse */
        $contentDetailRoomsResponse = app(ContentDetailRoomsResponse::class);

        $contentDetailRoomsResponse->setContentSupplier('');
        $contentDetailRoomsResponse->setUnifiedRoomCode('');
        $contentDetailRoomsResponse->setSupplierRoomId(0);
        $contentDetailRoomsResponse->setSupplierRoomName('');
        $contentDetailRoomsResponse->setSupplierRoomCode('');
        $contentDetailRoomsResponse->setAmenities([]);
        $contentDetailRoomsResponse->setImages([]);
        $contentDetailRoomsResponse->setDescriptions('');
        $contentDetailRoomsResponse->setContentSupplier('');
        $contentDetailRoomsResponse->setRelatedRooms([]);
        $contentDetailRoomsResponse->setSupplierCodes([]);

        return $contentDetailRoomsResponse;
    }
}
