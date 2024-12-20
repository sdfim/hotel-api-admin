<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigJobDescription;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigJobDescriptionController extends BaseWithPolicyController
{
    protected static string $model = ConfigJobDescription::class;

    private array $message = ['edit' => 'Edit Department', 'create' => 'Create Department'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.job-descriptions.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configJobDescription = ConfigJobDescription::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.job-descriptions.form', compact('configJobDescription', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configJobDescription = new ConfigJobDescription();
        $text = $this->message;

        return view('dashboard.configurations.job-descriptions.form', compact('configJobDescription', 'text'));
    }
}
