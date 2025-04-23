<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigDescriptiveTypeController extends BaseWithPolicyController
{
    protected static string $model = ConfigDescriptiveType::class;

    private array $message = ['edit' => 'Edit Descriptive Type', 'create' => 'Create Descriptive Type'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.descriptive-types.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configDescriptiveType = ConfigDescriptiveType::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.descriptive-types.form', compact('configDescriptiveType', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configDescriptiveType = new ConfigDescriptiveType();
        $text = $this->message;

        return view('dashboard.configurations.descriptive-types.form', compact('configDescriptiveType', 'text'));
    }
}
