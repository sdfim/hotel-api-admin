<?php

namespace Modules\HotelContentRepository\Actions\RoomLinkGroup;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

trait HandlesRoomLinks
{
    /**
     * Updates an existing productRoomLink containing the given room ID,
     * or creates a new one if it does not exist.
     *
     * @param  Model  $model  Model instance that has a productRoomLinks() relation
     * @param  array<int|string>  $rooms  Full set of room IDs to store in the link
     * @param  int  $roomId  Room ID to search for inside the link
     */
    private function updateOrCreateProductRoomLink(Model $model, array $rooms, int $roomId, string $groupUuid = null): void
    {
        if (! $groupUuid) {
            $groupUuid = Str::uuid()->toString();
        }
        $link = $model->productRoomLinks()
            ->whereJsonContains('rooms', [$roomId])
            ->where('group_uuid', $groupUuid)
            ->first();

        if ($link) {
            $link->rooms = $rooms;
            $link->save();
        } else {
            $model->productRoomLinks()->create([
                'rooms' => $rooms,
                'group_uuid' => $groupUuid,
            ]);
        }
    }

    /**
     * Deletes outdated room entities for the given model,
     * if their linked rooms are no longer present in the current list.
     *
     * @param  Model  $model  Model instance that has a productRoomLinks() relation
     * @param  array<int|string>  $currentRoomLinks  List of current/valid room IDs
     */
    private function deleteMissingRoomEntity(Model $model, array $currentRoomLinks, string $groupUuid): void
    {
        $dbRoomLinks = $model->productRoomLinks()
            ->where('group_uuid', $groupUuid)
            ->pluck('rooms')
            ->flatten()
            ->unique()
            ->toArray();

        $class = get_class($model);

        $class::with(['productRoomLinks' => function ($query) use ($groupUuid) {
            $query->where('group_uuid', $groupUuid);
        }])
            ->where('product_id', $model->product_id)
            ->where('rate_id', $model->rate_id)
            ->whereNotNull('room_id')
            ->whereNotIn('room_id', $currentRoomLinks)
            ->each(function ($affiliation) use ($dbRoomLinks) {
                $matched = $affiliation->productRoomLinks->filter(function ($link) use ($dbRoomLinks) {
                    return is_array($link->rooms) && count($link->rooms) === count($dbRoomLinks)
                        && empty(array_diff($link->rooms, $dbRoomLinks)) && empty(array_diff($dbRoomLinks, $link->rooms));
                })->isNotEmpty();
                if ($matched) {
                    $affiliation->delete();
                }
            });
    }

}
