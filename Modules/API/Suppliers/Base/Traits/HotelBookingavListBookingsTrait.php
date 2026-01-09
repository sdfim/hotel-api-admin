<?php

namespace Modules\API\Suppliers\Base\Traits;

use App\Models\ApiBookingInspector;
use App\Models\Supplier;
use App\Repositories\ChannelRepository;

trait HotelBookingavListBookingsTrait
{
    public function listBookings(): ?array
    {
        $tokenId = ChannelRepository::getTokenId(request()->bearerToken());
        $supplier = $this->supplier();
        $supplierId = Supplier::where('name', $supplier->value)->value('id');

        $apiClientId = data_get(request()->all(), 'api_client.id');
        $apiClientEmail = data_get(request()->all(), 'api_client.email');

        $itemsBooked = ApiBookingInspector::query()
            ->where('token_id', $tokenId)
            ->where('supplier_id', $supplierId)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->when(filled($apiClientId), fn ($q) => $q->whereJsonContains('request->api_client->id', (int) $apiClientId))
            ->when(filled($apiClientEmail), fn ($q) => $q->whereJsonContains('request->api_client->email', (string) $apiClientEmail))
            ->has('metadata')
            ->orderBy('created_at', 'desc')
            ->limit($this->getListBookingsLimit())
            ->get();

        $data = [];
        foreach ($itemsBooked as $item) {
            $filters['booking_id'] = $item->metadata?->booking_id;
            $data[] = $this->retrieveBooking($filters, $item->metadata, $supplier);
        }

        return $data;
    }

    protected function getListBookingsLimit(): int
    {
        return 10;
    }
}
