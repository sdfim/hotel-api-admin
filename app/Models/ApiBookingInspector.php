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

    protected $table = 'api_booking_inspector';

    protected $fillable = [
        'id',
        'booking_id',
        'token_id',
        'search_id',
        'supplier_id',
        'search_type',
        'type',
        'sub_type',
        'request',
        'response_path',
        'client_response_path'
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function getLinckDeleteItem($filters): string|null
    {
        $booking_id = $filters['booking_id'];
        $room_id = $filters['room_id'];

        $inspector = ApiBookingInspector::where('type', 'add_item')
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
            ->first();

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

    public function getLinckPutMetod($filters): string|null
    {
        $booking_id = $filters['booking_id'];
        $room_id = $filters['room_id'];

        $inspector = ApiBookingInspector::where('type', 'add_item')
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

    public function getItineraryId($filters)
    {
        $booking_id = $filters['booking_id'];

        $inspector = ApiBookingInspector::where('type', 'add_item')
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));

        return $json_response->itinerary_id;
    }

    public function getSearchId($filters)
    {
        $booking_id = $filters['booking_id'];

        $inspector = ApiBookingInspector::where('type', 'add_item')
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        return $inspector->search_id;
    }

    public function getLinckRetrieveItem($booking_id): string|null
    {
        $inspector = ApiBookingInspector::where('type', 'add_item')
            ->where('sub_type', 'like', 'create' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));

        return $json_response->links->retrieve->href;
    }

    public function getAffiliateReferenceIdByCannel($cannel): array|null
    {
        $inspectors = ApiBookingInspector::where('token_id', $cannel)
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

    public function geTypeSupplierByBookingId(string $booking_id): array
    {
        $search = ApiBookingInspector::where('booking_id', $booking_id)->first();
        return ['type' => $search->search_type, 'supplier' => $search->supplier->name];
    }
}
