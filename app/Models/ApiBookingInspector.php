<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;

class ApiBookingInspector extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'api_booking_inspector';

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'booking_id',
        'token_id',
        'search_id',
        'supplier_id',
        'search_type',
		'booking_item',
        'type',
        'sub_type',
        'request',
        'response_path',
        'client_response_path'
    ];

    /**
     * @return BelongsTo
     */
    public function token(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

    /**
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

	protected static function boot()
    {
        parent::boot();

        static::deleted(function ($model) {
            Storage::delete($model->response_path);
			Storage::delete($model->client_response_path);
        });
    }

    /**
     * @param string $booking_id
	 * @param int $room_id
     * @return string|null
     */
    public function getLinkDeleteItem(string $booking_id, int $room_id): string|null
    {
        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
            ->first();

		if (!isset($inspector)) return null;

        $json_response = json_decode(Storage::get($inspector->response_path));
        $rooms = $json_response->rooms;

        $linkDeleteItem = '';
        foreach ($rooms as $room) {
            if ($room->id == $room_id) {
                $linkDeleteItem = $room->links->cancel->href;
                break;
            }
        }

        return $linkDeleteItem;
    }

    /**
     * @param string $booking_id
	 * @param int $room_id
     * @return string|null
     */
    public function getLinkPutMethod(string $booking_id, int $room_id): string|null
    {
        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));
        $rooms = $json_response->rooms;

        $linkPutMethod = '';
        foreach ($rooms as $room) {
            if ($room->id == $room_id) {
                $linkPutMethod = $room->links->change->href;
                break;
            }
        }

        return $linkPutMethod;
    }

    /**
     * @param $filters
     * @return string|null
     */
    public function getItineraryId($filters): null|string
    {
        $booking_id = $filters['booking_id'];

        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));

        return $json_response->itinerary_id;
    }

    /**
     * @param $filters
     * @return string|null
     */
    public function getSearchId($filters): null|string
    {
        $booking_id = $filters['booking_id'];

        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        return $inspector->search_id;
    }

    /**
     * @param $booking_id
     * @return string|null
     */
    public function getLinkRetrieveItem($booking_id): string|null
    {
        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'create' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));

        return $json_response->links->retrieve->href;
    }

    /**
     * @param $channel
     * @return array|null
     */
    public function getAffiliateReferenceIdByChannel($channel): array|null
    {
        $inspectors = ApiBookingInspector::where('token_id', $channel)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('type', 'add_item')
                        ->where('sub_type', 'like', 'retrieve' . '%');
                })// ->orWhere('type', 'retrieve_items')
                ;
            })
            ->get();

        $list = [];
        foreach ($inspectors as $inspector) {
            $json_response = json_decode(Storage::get($inspector->response_path));
            if (isset($json_response->affiliate_reference_id)) {
                $list[] = [
                    'affiliate_reference_id' => $json_response->affiliate_reference_id,
                    'email' => $json_response->email
                ];
            }
        }

        return $list;
    }

    /**
     * @param string $booking_id
     * @return array
     */
    public function geTypeSupplierByBookingId(string $booking_id): array
    {
        $search = ApiBookingInspector::where('booking_id', $booking_id)->first();
        return ['type' => $search->search_type, 'supplier' => $search->supplier->name];
    }
}
