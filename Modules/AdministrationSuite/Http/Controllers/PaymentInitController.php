<?php

namespace Modules\AdministrationSuite\Http\Controllers;

use App\Models\ApiBookingPaymentInit;
use Illuminate\View\View;

class PaymentInitController extends BaseWithPolicyController
{
    protected static string $model = ApiBookingPaymentInit::class;

    public function index(): View
    {
        return view('dashboard.payment-inspector.index');
    }
}

