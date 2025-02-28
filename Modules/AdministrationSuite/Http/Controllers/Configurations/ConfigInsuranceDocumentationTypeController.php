<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigInsuranceDocumentationType;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigInsuranceDocumentationTypeController extends BaseWithPolicyController
{
    protected static string $model = ConfigInsuranceDocumentationType::class;

    private array $message = ['edit' => 'Edit Insurance Documentation Type', 'create' => 'Create Insurance Documentation Type'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.insurance_documentation_types.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configInsuranceDocumentationType = ConfigInsuranceDocumentationType::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.insurance_documentation_types.form', compact('configInsuranceDocumentationType', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configInsuranceDocumentationType = new ConfigInsuranceDocumentationType();
        $text = $this->message;

        return view('dashboard.configurations.insurance_documentation_types.form', compact('configInsuranceDocumentationType', 'text'));
    }
}
