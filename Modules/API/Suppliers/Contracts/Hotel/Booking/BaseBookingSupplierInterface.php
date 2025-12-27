<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Booking;

use App\Models\ApiBookingInspector;

interface BaseBookingSupplierInterface
{
    public function addItem(array $filters, string $supplierName, string $type = 'add_item', array $headers = []): ?array;

    public function removeItem(array $filters): array;

    public function retrieveItem(ApiBookingInspector $bookingInspector): ?array;
}
