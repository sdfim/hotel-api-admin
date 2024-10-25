<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigServiceTypeController extends BaseWithPolicyController
{
    protected static string $model = ConfigServiceType::class;

    private array $message = ['edit' => 'Edit Service Type', 'create' => 'Create Service Type'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.service-types.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configServiceType = ConfigServiceType::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.service-types.form', compact('configServiceType', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configServiceType = new ConfigServiceType();
        $text = $this->message;

        return view('dashboard.configurations.service-types.form', compact('configServiceType', 'text'));
    }
}
