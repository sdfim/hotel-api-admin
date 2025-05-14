<?php

namespace Modules\HotelContentRepository\Actions\HotelRoom;

use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\HotelRoomMerge;

class MergeHotelRoom
{
    public function execute(HotelRoom $toRoom, HotelRoom $fromRoom): ?HotelRoom
    {
        // Initialize newRoomData with the data from toRoom
        $newRoomData = $toRoom->toArray();

        if (empty($newRoomData['description'])) {
            $newRoomData['description'] = $fromRoom->description;
        }
        // Create the new room
        $newRoom = HotelRoom::create($newRoomData);

        return $this->merge([$toRoom->id, $fromRoom->id], $newRoom);
    }

    /**
     * Merge multiple hotel rooms into a new room.
     */
    public function merge(array $roomIds, HotelRoom $newRoom): ?HotelRoom
    {
        return DB::transaction(function () use ($roomIds, $newRoom) {
            // Fetch the rooms to be merged
            $oldRooms = HotelRoom::whereIn('id', $roomIds)->get();

            if ($oldRooms->isEmpty()) {
                return null;
            }

            $overwrittenFields = [
                'belongsToMany' => [],
                'hasMany' => [],
            ];

            // Handle BelongsToMany relationships
            foreach (['rates', 'galleries', 'attributes', 'relatedRooms'] as $relation) {
                foreach ($oldRooms as $room) {
                    foreach ($room->$relation as $related) {
                        if (! $newRoom->$relation->contains($related->id)) {
                            $newRoom->$relation()->attach($related->id);
                            $overwrittenFields['belongsToMany'][$room->id][$relation][] = $related->id;
                        }
                    }
                }
            }

            // Handle HasMany relationships
            foreach (['affiliations', 'consortiaAmenities', 'feeTaxes', 'informativeServices'] as $relation) {
                foreach ($oldRooms as $room) {
                    $relatedItems = $room->$relation()->get();
                    foreach ($relatedItems as $related) {
                        $related->update(['room_id' => $newRoom->id]);
                        $overwrittenFields['hasMany'][$room->id][$relation][] = $related->id;
                    }
                }
            }

            // Create a record in HotelRoomMerge for each old room
            HotelRoomMerge::create([
                'parent_room_id' => $roomIds[0],
                'child_room_id' => $roomIds[1],
                'new_room_id' => $newRoom->id,
                'overwritten_fields' => $overwrittenFields,
            ]);

            // Soft delete old rooms
            foreach ($oldRooms as $room) {
                $room->delete();
            }

            return $newRoom;
        });
    }

    /**
     * Undo the merge of hotel rooms.
     */
    public function rollback(HotelRoom $newRoom): bool
    {
        return DB::transaction(function () use ($newRoom) {
            // Fetch the merge record
            $mergeRecord = HotelRoomMerge::where('new_room_id', $newRoom->id)->first();

            if (! $mergeRecord) {
                return false;
            }

            // Restore the old rooms
            $parentRoom = HotelRoom::withTrashed()->find($mergeRecord->parent_room_id);
            $childRoom = HotelRoom::withTrashed()->find($mergeRecord->child_room_id);

            if ($parentRoom) {
                $parentRoom->restore();
            }

            if ($childRoom) {
                $childRoom->restore();
            }

            // Restore BelongsToMany relationships
            foreach ($mergeRecord->overwritten_fields['belongsToMany'] as $roomId => $relations) {
                $room = HotelRoom::find($roomId);
                foreach ($relations as $relation => $relatedIds) {
                    $room->$relation()->syncWithoutDetaching($relatedIds);
                }
            }

            // Restore HasMany relationships
            foreach ($mergeRecord->overwritten_fields['hasMany'] as $roomId => $relations) {
                foreach ($relations as $relation => $relatedIds) {
                    foreach ($relatedIds as $relatedId) {
                        $relatedModel = $newRoom->$relation()->find($relatedId);
                        if ($relatedModel) {
                            $relatedModel->update(['room_id' => $roomId]);
                        }
                    }
                }
            }

            // Delete the new room
            $newRoom->delete();

            // Delete the merge record
            $mergeRecord->delete();

            return true;
        });
    }
}
