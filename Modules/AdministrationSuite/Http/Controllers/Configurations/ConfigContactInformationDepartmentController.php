<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigContactInformationDepartment;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigContactInformationDepartmentController extends BaseWithPolicyController
{
    protected static string $model = ConfigContactInformationDepartment::class;

    private array $message = ['edit' => 'Edit Inrernal Department', 'create' => 'Create Inrernal Department'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.contact-information-departments.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configContactInformationDepartment = ConfigContactInformationDepartment::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.contact-information-departments.form', compact('configContactInformationDepartment', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configContactInformationDepartment = new ConfigContactInformationDepartment();
        $text = $this->message;

        return view('dashboard.configurations.contact-information-departments.form', compact('configContactInformationDepartment', 'text'));
    }
}
