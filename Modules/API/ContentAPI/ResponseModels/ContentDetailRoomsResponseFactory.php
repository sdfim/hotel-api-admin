<?php

namespace Modules\API\ContentAPI\ResponseModels;

class ContentDetailRoomsResponseFactory
{
    public static function create(): ContentDetailRoomsResponse
    {
        $contentDetailRoomsResponse = new ContentDetailRoomsResponse();

        $contentDetailRoomsResponse->setContentSupplier('');
        $contentDetailRoomsResponse->setSupplierRoomId(0);
        $contentDetailRoomsResponse->setSupplierRoomName('');
        $contentDetailRoomsResponse->setSupplierRoomCode('');
        $contentDetailRoomsResponse->setAmenities([]);
        $contentDetailRoomsResponse->setImages([]);
        $contentDetailRoomsResponse->setDescriptions('');
        $contentDetailRoomsResponse->setContentSupplier('');


        return $contentDetailRoomsResponse;
    }
}
