<?php

namespace Modules\API\Controllers\ApiHendlers;
use Modules\API\BaseController;
use Modules\API\Controllers\ApiHandlerInterface;

class ComboApiHandler extends BaseController implements ApiHandlerInterface
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