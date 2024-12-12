<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;
use Modules\HotelContentRepository\Models\Vendor;
use Illuminate\Contracts\View\View;

class VendorController extends BaseWithPolicyController
{
    protected static string $model = Vendor::class;
    protected static ?string $parameterName = 'vendor_repository';

    private array $message = ['edit' => 'Edit', 'create' => 'Create'];

    public function index(): View
    {
        return view('dashboard.hotel_repository.vendors.index');
    }

    public function edit(string $id): View
    {
        $vendor = Vendor::findOrFail($id);
        $text = $this->message;

        return view('dashboard.hotel_repository.vendors.form', compact('vendor', 'text'));
    }

    public function create(): View
    {
        $vendor = new Vendor();
        $text = $this->message;
        return view('dashboard.hotel_repository.vendors.form', compact('vendor','text'));
    }
}
