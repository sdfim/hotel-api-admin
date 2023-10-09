<?php

namespace Modules\API\Controllers\ApiHendlers;
use Modules\API\Controllers\ApiHandlerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\API\BaseController;
use App\Models\Suppliers;
use Modules\API\Controllers\ExpediaHotelApiHandler;
use Modules\API\Requests\SearchHotelRequest;
use Modules\API\Suppliers\ExpediaSupplier\ExperiaService;
use Illuminate\Support\Facades\Validator;


class FlightApiHandler extends BaseController implements ApiHandlerInterface
{
	private const SUPPLIER_NAME = 'Expedia';

	public function search(Request $request, array $supplierIds) : JsonResponse
	{	

	}

	public function detail(Request $request, array $supplierIds) : JsonResponse
	{

	}

	public function price(Request $request, array $supplierIds) : JsonResponse
	{

	}
}