<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigConsortium;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigConsortiumController extends BaseWithPolicyController
{
    protected static string $model = ConfigConsortium::class;

    private array $message = ['edit' => 'Edit Consortium', 'create' => 'Create Consortium'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.consortia.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configConsortium = ConfigConsortium::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.consortia.form', compact('configConsortium', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configConsortium = new ConfigConsortium();
        $text = $this->message;

        return view('dashboard.configurations.consortia.form', compact('configConsortium', 'text'));
    }
}
