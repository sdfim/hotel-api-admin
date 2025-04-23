<?php

namespace Modules\AdministrationSuite\Http\Controllers\Configurations;

use App\Models\Configurations\ConfigAttribute;
use Illuminate\View\View;
use Modules\AdministrationSuite\Http\Controllers\BaseWithPolicyController;

class ConfigAttributeController extends BaseWithPolicyController
{
    protected static string $model = ConfigAttribute::class;

    private array $message = ['edit' => 'Edit Attribute', 'create' => 'Create Attribute'];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('dashboard.configurations.attributes.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $configAttribute = ConfigAttribute::findOrFail($id);
        $text = $this->message;

        return view('dashboard.configurations.attributes.form', compact('configAttribute', 'text'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $configAttribute = new ConfigAttribute();
        $text = $this->message;

        return view('dashboard.configurations.attributes.form', compact('configAttribute', 'text'));
    }
}
