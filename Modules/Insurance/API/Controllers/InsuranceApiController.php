<?php

namespace Modules\Insurance\API\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\API\BaseController;
use Modules\Insurance\API\Requests\InsuaranceAddRequest;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceProvider;

class InsuranceApiController extends BaseController
{
    public function add(InsuaranceAddRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $insurancePlan = new InsurancePlan();
        $insurancePlan->booking_item = $validated['booking_item'];
        // определяем из booking_item * %
       /*
        Separate Table with Frontend *:
        We could set up a separate table for the price ranges(X Price & Y Price) and the insurance markup, with a simple frontend to manage these ranges .
        This would make it easy to add or change them when needed .
       */
//        $insurancePlan->total_insurance_cost = ;
//        $insurancePlan->supplier_fee = ;
//        $insurancePlan->commission_ujv = ;

        $insurancePlan->insurance_provider_id = InsuranceProvider::where('name', $validated['insurance_provider'])->pluck('id')->first();

        if ($insurancePlan->save()) {
            return $this->sendResponse($insurancePlan->toArray(), 'Insurance plan added successfully', 201);
        } else {
            return $this->sendError('Failed to add insurance plan', 500);
        }
    }
}
