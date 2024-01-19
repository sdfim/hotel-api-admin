<?php

namespace Modules\API\Tools;

class GeneralTools
{
    /**
     * @param array $rooms
     * @return int
     */
    public function calcTotalNumberOfGuestsInAllRooms(array $rooms): int
    {
        $totalNumberOfGuests = 0;

        foreach ($rooms as $room) {
            foreach ($room as $roomGuestsNumber) {
                $totalNumberOfGuests += (int)$roomGuestsNumber;
            }
        }

        return $totalNumberOfGuests;
    }
}
