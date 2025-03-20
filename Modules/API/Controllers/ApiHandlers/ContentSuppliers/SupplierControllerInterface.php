<?php

namespace Modules\API\Controllers\ApiHandlers\ContentSuppliers;

use Illuminate\Http\Request;

interface SupplierControllerInterface
{
    public function search(array $filters): array;
    public function detail(Request $request): array|object;
}
