<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigAmenity;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigAmenityController extends BaseWithPolicyController
{
    protected static string $model = ConfigAmenity::class;

    private array $message = ['edit' => 'Edit Amenity', 'create' => 'Create Amenity'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.amenities.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configAmenity = ConfigAmenity::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.amenities.form', compact('configAmenity', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configAmenity = new ConfigAmenity();
        $text = $this->message;

        return view('dashboard.configurations.amenities.form', compact('configAmenity', 'text'));
    }
}
